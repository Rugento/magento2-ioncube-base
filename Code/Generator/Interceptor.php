<?php
namespace Ioncube\Di\Code\Generator;

class Interceptor extends \Magento\Framework\Interception\Code\Generator\Interceptor
{
    /**
     * Whether method is intercepted
     *
     * @param \ReflectionMethod $method
     * @return bool
     */
    protected function isInterceptedMethod(\ReflectionMethod $method)
    {
        $declaringNamespace = $method->getDeclaringClass()->getNamespaceName();
        return !(substr_count($declaringNamespace, 'Rugento') || $method->isConstructor() || $method->isFinal() || $method->isStatic() || $method->isDestructor()) &&
        !in_array($method->getName(), ['__sleep', '__wakeup', '__clone']);
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        $reflectionClass = new \ReflectionClass($this->getSourceClassName());
        $constructor = $reflectionClass->getConstructor();
        $parameters = [];
        $body = "\$this->___init();\n";
        if ($constructor) {
            if($reflectionClass->hasMethod('__precompile')) {
                $preCompiledParameters = $reflectionClass->getMethod('__precompile')->invoke(null);
                foreach ($constructor->getParameters() as $parameter) {
                    $parameters[] = $this->_getMethodParameterInfoPreCompiled($parameter, $preCompiledParameters);
                }
            } else {
                foreach ($constructor->getParameters() as $parameter) {
                    $parameters[] = $this->_getMethodParameterInfo($parameter);
                }
            }
            $body .= count($parameters)
            ? "parent::__construct({$this->_getParameterList($parameters)});"
            : "parent::__construct();";
        }
        return [
            'name' => '__construct',
            'parameters' => $parameters,
            'body' => $body
        ];
    }

    /**
     * Retrieve method parameter info
     *
     * @param \ReflectionParameter $parameter
     * @return array
     */
    protected function _getMethodParameterInfoPreCompiled(\ReflectionParameter $parameter, $preCompiledParameters)
    {
        $parameterInfo = [
            'name' => $parameter->getName(),
            'passedByReference' => $parameter->isPassedByReference(),
            'type' => $parameter->getType()
        ];

        if ($parameter->isArray()) {
            $parameterInfo['type'] = 'array';
        } elseif ($parameter->getClass()) {
            $parameterInfo['type'] = $this->_getFullyQualifiedClassName($parameter->getClass()->getName());
        } elseif ($parameter->isCallable()) {
            $parameterInfo['type'] = 'callable';
        }

        $defaultValue = $this->_getDefaultValue($parameter, $preCompiledParameters);
        if ($parameter->isOptional()) {
            if (is_string($defaultValue)) {
                $parameterInfo['defaultValue'] = $parameter->getDefaultValue();
            } elseif ($defaultValue === null) {
                $parameterInfo['defaultValue'] = $this->_getNullDefaultValue();
            } else {
                $parameterInfo['defaultValue'] = $defaultValue;
            }
        }

        return $parameterInfo;
    }

    /**
     * @param unknown $preCompiledParameters
     */
    private function _getDefaultValue($parameter, $preCompiledParameters)
    {
        foreach ($preCompiledParameters as $_parameter) {
            if($parameter->getName() == $_parameter[0]) {
                return $_parameter[3];
            }
        }
        return $parameter->getDefaultValue();
    }
}