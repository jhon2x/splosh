<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin\SalesRule\Model;

use Magento\Quote\Model\Quote\Address as Address;
use Magento\SalesRule\Model\Rule as Rule;
use Magento\SalesRule\Model\RulesApplier as SalesRulesApplier;
use Amasty\Rules\Model\DiscountRegistry as DiscountRegistry;

/**
 * Entry point for @see \Amasty\Rules\Model\DiscountRegistry::setShippingDiscount.
 */
class RulesApplier
{
    /**
     * @var DiscountRegistry
     */
    private $discountRegistry;

    public function __construct(
        DiscountRegistry $discountRegistry
    ) {
        $this->discountRegistry = $discountRegistry;
    }

    /**
     * @param SalesRulesApplier $subject
     * @param Address $address
     * @param Rule $rule
     * @param string|null $couponCode
     */
    public function beforeMaintainAddressCouponCode(
        SalesRulesApplier $subject,
        Address $address,
        Rule $rule,
        $couponCode
    ) {
        if ($address->getShippingDiscountAmount() > 0) {
            $this->discountRegistry->setShippingDiscount($rule->getRuleId(), $address->getShippingDiscountAmount());
        }
    }
}
