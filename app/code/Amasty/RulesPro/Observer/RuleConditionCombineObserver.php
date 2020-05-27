<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */


namespace Amasty\RulesPro\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class RuleConditionCombineObserver
 * @codingStandardsIgnoreFile
 */
class RuleConditionCombineObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getAdditional();
        $cond = $transport->getConditions();
        if (!is_array($cond)) {
            $cond = [];
        }

        $types = [
            'Orders' => 'Purchases history',
        ];

        if (!$this->moduleManager->isOutputEnabled('Amasty_Conditions')) {
            $types['Customer'] = 'Customer attributes';
        }

        foreach ($types as $typeCode => $typeLabel) {
            $condition = $this->_objectManager->get('Amasty\RulesPro\Model\Rule\Condition\\' . $typeCode);
            $conditionAttributes = $condition->loadAttributeOptions()->getAttributeOption();

            $attributes = [];
            foreach ($conditionAttributes as $code => $label) {
                $attributes[] = [
                    'value' => 'Amasty\RulesPro\Model\Rule\Condition\\' . $typeCode . '|' . $code,
                    'label' => $label,
                ];
            }
            $cond[] = [
                'value' => $attributes,
                'label' => __($typeLabel),
            ];
        }

        $cond[] = [
            'value' => \Amasty\RulesPro\Model\Rule\Condition\Total::class,
            'label' => __('Orders Subselection')
        ];

        $transport->setConditions($cond);

        return $this;
    }
}
