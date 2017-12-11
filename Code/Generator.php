<?php
namespace Ioncube\Di\Code;

use Magento\Framework\Code\Generator as FrameworkGenerator;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\ObjectManagerInterface;

class Generator extends \Magento\Setup\Module\Di\Code\Generator
{
    /**
     * List of class methods
     *
     * @var array
     */
    private $classMethods = [];

    /**
     * @param ObjectManagerInterface $objectManagerInterface
     * @param FrameworkGenerator\Io $ioObject
     * @param array $generatedEntities
     * @param DefinedClasses $definedClasses
     */
    public function __construct(
        ObjectManagerInterface $objectManagerInterface,
        \Magento\Framework\Code\Generator\Io $ioObject = null,
        array $generatedEntities = [],
        DefinedClasses $definedClasses = null
        ) {
            if(is_array($generatedEntities) && key_exists('interceptor', $generatedEntities) && !empty($generatedEntities['interceptor']) && $generatedEntities['interceptor'] == 'Magento\Setup\Module\Di\Code\Generator\Interceptor') {
                $generatedEntities['interceptor'] = 'Ioncube\Di\Code\Setup\Module\Di\Code\Generator\Interceptor';
            }
            parent::__construct($objectManagerInterface, $ioObject, $generatedEntities, $definedClasses);
            $this->setObjectManager($objectManagerInterface);
    }

    /**
     * Sets class methods
     *
     * @param array $methods
     * @return void
     */
    private function setClassMethods($methods)
    {
        $this->classMethods = $methods;
    }

    /**
     * Clear class methods
     * @return void
     */
    private function clearClassMethods()
    {
        $this->classMethods = [];
    }
}