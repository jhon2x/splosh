<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin;

use Amasty\Rules\Api\Data\RuleInterface;

/**
 * Replace Amasty Rule with data instead of string array.
 */
class SalesRule
{
    /**
     * @var \Amasty\Rules\Model\RuleFactory
     */
    private $ruleFactory;

    public function __construct(
        \Amasty\Rules\Model\RuleFactory $ruleFactory
    ) {
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $subject
     * @param \Magento\SalesRule\Model\Rule $salesRule
     *
     * @return \Magento\SalesRule\Model\Rule
     */
    public function afterLoadPost(\Magento\SalesRule\Model\Rule $subject, $salesRule)
    {
        /** @var array $attributes */
        $attributes = $salesRule->getExtensionAttributes() ?: [];

        if (!isset($attributes[RuleInterface::EXTENSION_CODE])
            || !is_array($attributes[RuleInterface::EXTENSION_CODE])
        ) {
            return $salesRule;
        }

        /** @var RuleInterface $amRule */
        $amRule = $this->ruleFactory->create();
        $amRule->addData($attributes[RuleInterface::EXTENSION_CODE]);

        $attributes[RuleInterface::EXTENSION_CODE] = $amRule;
        $subject->setExtensionAttributes($attributes);

        return $salesRule;
    }
}
