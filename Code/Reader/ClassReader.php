<?php
namespace Ioncube\Di\Code\Reader;

class ClassReader extends \Magento\Framework\Code\Reader\ClassReader
{

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleReader
        ) {
            $this->moduleReader = $moduleReader;
    }


    /**
     * Read class constructor signature
     *
     * @param string $className
     * @return array|null
     * @throws \ReflectionException
     */
    public function getConstructor($className)
    {
        $class       = new \ReflectionClass($className);
        $result      = null;
        $constructor = $class->getConstructor();
        if ($constructor) {
            $result = [];

           //XXX
            $classType = explode('\\', $className);
            if (count($classType) > 1 && !in_array($classType[0], ['Magento', 'Composer', 'Symfony'])) {
                if($class->hasMethod('__precompile')) {
                    return $class->getMethod('__precompile')->invoke(null);
                }
                $modDir = $this->moduleReader->getModuleDir(
                    \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
                    $classType[0].'_'.$classType[1]
                    );
                if ($modDir != '/etc') {
                    if (is_file($modDir.DIRECTORY_SEPARATOR.'deps.json')) {
                        $jsonData = file_get_contents($modDir.DIRECTORY_SEPARATOR.'deps.json');
                        if ($jsonData !== false) {
                            $json = json_decode($jsonData, true);
                            if (isset($json[$className])) {
                                return $json[$className];
                            }
                        }
                    }
                }
            }
            //XXX

            /** @var $parameter \ReflectionParameter */
            foreach ($constructor->getParameters() as $parameter) {
                try {
                    $result[] = [
                        $parameter->getName(),
                        $parameter->getClass() !== null ? $parameter->getClass()->getName() : null,
                        !$parameter->isOptional(),
                        $parameter->isOptional()
                        ? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null)
                        : null,
                    ];
                } catch (\ReflectionException $e) {
                    $message = $e->getMessage();
                    throw new \ReflectionException($message, 0, $e);
                }
            }
        }

        return $result;
    }
}