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

/** @var \Magento\SalesRule\Model\Rule $salesRule */
$salesRule = $objectManager->create(\Magento\SalesRule\Model\Rule::class);
$salesRule->setData(
    [
        'name' => 'Buy X get Y With 2 Products Get Dicsount Amount 30',
        'is_active' => 1,
        'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'simple_action' => 'buyxgetn_fixdisc',
        'discount_amount' => 30,
        'discount_step' => 2,
        'stop_rules_processing' => 1,
        'website_ids' => [
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getWebsite()->getId()
        ]
    ]
);
$salesRule->save();
$registry->unregister('Magento/SalesRule/_files/cart_rule_buy_x_gey_y_30_discount_amount');
$registry->register('Magento/SalesRule/_files/cart_rule_buy_x_gey_y_30_discount_amount', $salesRule->getRuleId());
