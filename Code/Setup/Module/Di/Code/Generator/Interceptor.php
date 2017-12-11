<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ioncube\Di\Code\Setup\Module\Di\Code\Generator;

use Ioncube\Di\Code\Generator\Interceptor as FrameworkInterceptor;

class Interceptor extends FrameworkInterceptor
{
    /**
     * Intercepted methods list
     *
     * @var array
     */
    private $interceptedMethods = [];

    /**
     * Whether method is intercepted
     *
     * @param \ReflectionMethod $method
     *
     * @return bool
     */
    protected function isInterceptedMethod(\ReflectionMethod $method)
    {
        return parent::isInterceptedMethod($method) && in_array($method->getName(), $this->interceptedMethods);
    }

    /**
     * Sets list of intercepted methods
     *
     * @param array $interceptedMethods
     *
     * @return void
     */
    public function setInterceptedMethods($interceptedMethods)
    {
        $this->interceptedMethods = $interceptedMethods;
    }
}