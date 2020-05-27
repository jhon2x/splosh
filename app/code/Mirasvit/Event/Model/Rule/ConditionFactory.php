<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Model\Rule;


use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Event\Service\Config\Map;

class ConditionFactory extends \Magento\Rule\Model\ConditionFactory
{
    /**
     * @var array
     */
    private $virtualConditionModels = [];

    /**
     * @var ObjectManagerInterface
     */
    private $om;

    /**
     * ConditionFactory constructor.
     *
     * @param Map                    $map
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Map $map, ObjectManagerInterface $objectManager) {
        parent::__construct($objectManager);

        $this->om = $objectManager;
        $map->loadConditions($this);
    }

    /**
     * Add virtual condition.
     *
     * @param string $class - name of the virtual condition model
     */
    public function addCondition($class)
    {
        $this->virtualConditionModels[$class] = $this->om->get($class);
    }

    /**
     * Add support for virtual types.
     *
     * {@inheritdoc}
     */
    public function create($type)
    {
        if (isset($this->virtualConditionModels[$type])) {
            return $this->virtualConditionModels[$type];
        }

        return parent::create($type);
    }
}
