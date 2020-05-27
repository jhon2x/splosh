<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Observer\Salesrule;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Entry point for @see \Amasty\Rules\Model\DiscountRegistry::setDiscount
 */
class Discount implements ObserverInterface
{
    /**
     * @var \Amasty\Rules\Model\DiscountRegistry
     */
    private $discountRegistry;

    public function __construct(
        \Amasty\Rules\Model\DiscountRegistry $discountRegistry
    ) {
        $this->discountRegistry = $discountRegistry;
    }

    /**
     * @param Observer $observer
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data|void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $observer->getRule();
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $result */
        $result = $observer->getResult();
        /** @var \Magento\Quote\Model\Quote $item */
        $item = $observer->getItem();

        $this->discountRegistry->setDiscount($result, $rule, $item);
    }
}
