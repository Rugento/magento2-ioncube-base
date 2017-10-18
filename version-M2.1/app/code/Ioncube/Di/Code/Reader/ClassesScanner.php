<?php
namespace Ioncube\Di\Code\Reader;

use Magento\Framework\Exception\FileSystemException;
use Magento\Setup\Module\Di\Code\Reader\FileScanner;

class ClassesScanner extends \Magento\Setup\Module\Di\Code\Reader\ClassesScanner
{
    /**
     * Retrieves list of classes for given path
     *
     * @param string $path
     * @return array
     * @throws FileSystemException
     */
    public function getList($path)
    {
        $realPath = realpath($path);
        if (!(bool)$realPath) {
            throw new FileSystemException(new \Magento\Framework\Phrase('Invalid path: %1', [$path]));
        }

        //XXX
        if(is_file($path.DIRECTORY_SEPARATOR.'classmap.csv')) {
            $data = file_get_contents($path.DIRECTORY_SEPARATOR.'classmap.csv');
            if($data !== false) {
                return explode(':', trim($data));
            }
        }
        //XXX

        $recursiveIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($realPath, \FilesystemIterator::FOLLOW_SYMLINKS),
            \RecursiveIteratorIterator::SELF_FIRST
            );

        $classes = [];
        foreach ($recursiveIterator as $fileItem) {
            /** @var $fileItem \SplFileInfo */
            if ($fileItem->isDir() || $fileItem->getExtension() !== 'php') {
                continue;
            }
            foreach ($this->excludePatterns as $excludePatterns) {
                if ($this->isExclude($fileItem, $excludePatterns)) {
                    continue 2;
                }
            }
            $fileScanner = new FileScanner($fileItem->getRealPath());
            $classNames = $fileScanner->getClassNames();
            foreach ($classNames as $className) {
                if (!class_exists($className)) {
                    require_once $fileItem->getRealPath();
                }
                $classes[] = $className;
            }
        }
        return $classes;
    }

    /**
     * Find out if file should be excluded
     *
     * @param \SplFileInfo $fileItem
     * @param string $patterns
     * @return bool
     */
    private function isExclude(\SplFileInfo $fileItem, $patterns)
    {
        if (!is_array($patterns)) {
            $patterns = (array)$patterns;
        }
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, str_replace('\\', '/', $fileItem->getRealPath()))) {
                return true;
            }
        }
        return false;
    }
}