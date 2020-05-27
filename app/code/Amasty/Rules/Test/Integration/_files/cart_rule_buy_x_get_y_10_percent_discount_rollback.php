<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */

/**
 * @codingStandardsIgnoreFile
 * @codeCoverageIgnore
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$salesRuleId = $registry->registry('Magento/SalesRule/_files/cart_rule_buy_x_get_y_10_percent_discount');

if ($salesRuleId) {
    /** @var \Magento\SalesRule\Model\Rule $salesRule */
    $salesRule = $objectManager->create(\Magento\SalesRule\Model\Rule::class)->load($salesRuleId, 'rule_id');
    $salesRule->load($salesRuleId, 'rule_id');

    if ($salesRule->getRuleId()) {
        $salesRule->delete();
    }
}

$registry->unregister('Magento/SalesRule/_files/cart_rule_buy_x_get_y_10_percent_discount');
