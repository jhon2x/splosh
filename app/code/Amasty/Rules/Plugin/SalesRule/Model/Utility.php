<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin\SalesRule\Model;

use Magento\SalesRule\Model\Rule\Action\Discount\Data as DiscountData;
use Magento\SalesRule\Model\Utility as SalesRuleUtility;
use Magento\Quote\Model\Quote\Item\AbstractItem as AbstractItem;
use Amasty\Rules\Model\DiscountRegistry as DiscountRegistry;

/**
 * Entry point for @see \Amasty\Rules\Model\DiscountRegistry::fixDiscount.
 */
class Utility
{
    /**
     * @var DiscountData
     */
    private $discountData;

    /**
     * @var AbstractItem
     */
    private $item;

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
     * @param SalesRuleUtility $subject
     * @param DiscountData $discountData
     * @param AbstractItem $item
     * @param float $qty
     */
    public function beforeMinFix(
        SalesRuleUtility $subject,
        DiscountData $discountData,
        AbstractItem $item,
        $qty
    ) {
        $this->discountData = $discountData;
        $this->item = $item;
    }

    /**
     * @param SalesRuleUtility $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterMinFix($subject, $result)
    {
        $this->discountRegistry->fixDiscount($this->discountData, $this->item);

        return $result;
    }
}
