<?php
namespace Ioncube\Di\Code\Reader;

class ClassReader extends \Magento\Framework\Code\Reader\ClassReader
{
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