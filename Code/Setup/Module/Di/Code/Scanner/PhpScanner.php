<?php
namespace Ioncube\Di\Code\Setup\Module\Di\Code\Scanner;

use Magento\Setup\Module\Di\Compiler\Log\Log;

class PhpScanner extends \Magento\Setup\Module\Di\Code\Scanner\PhpScanner
{

    /**
     * Get classes and interfaces declared in the file
     *
     * @param string $file
     * @return array
     */
    protected function _getDeclaredClasses($file)
    {
        //XXX
        if(substr_count($file, 'Rugento') && !substr_count($file, 'registration.php')) {
            $explode = explode('Rugento', $file);
            $className = '\Rugento'.str_replace(['.php','/'], ['','\\'], $explode[1]);
            if(class_exists($className)) {
                return [$className];
            }
            return [];
        }
        //XXX

        $classes = [];
        $namespace = '';
        $tokens = token_get_all(file_get_contents($file));
        $count = count($tokens);

        for ($tokenIterator = 0; $tokenIterator < $count; $tokenIterator++) {
            if ($tokens[$tokenIterator][0] == T_NAMESPACE) {
                $namespace .= $this->_fetchNamespace($tokenIterator, $count, $tokens);
            }

            if (($tokens[$tokenIterator][0] == T_CLASS || $tokens[$tokenIterator][0] == T_INTERFACE)
                && $tokens[$tokenIterator - 1][0] != T_DOUBLE_COLON
                ) {
                    $classes = array_merge($classes, $this->_fetchClasses($namespace, $tokenIterator, $count, $tokens));
                }
        }
        return array_unique($classes);
    }

    /**
     * Check if specified class is missing and if it can be generated.
     *
     * @param string $missingClassName
     * @param string $entityType
     * @param string $file
     * @return bool
     */
    private function shouldGenerateClass($missingClassName, $entityType, $file)
    {
        try {
            if (class_exists($missingClassName)) {
                return false;
            }
        } catch (\RuntimeException $e) {
        }
        $sourceClassName = $this->getSourceClassName($missingClassName, $entityType);
        if (!class_exists($sourceClassName) && !interface_exists($sourceClassName)) {
            $this->_log->add(
                Log::CONFIGURATION_ERROR,
                $missingClassName,
                "Invalid {$entityType} for nonexistent class {$sourceClassName} in file {$file}"
                );
            return false;
        }
        return true;
    }
}
