<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model\Rule\Action\Discount;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;

/**
 * Amasty Rule calculation by action.
 *
 * @see \Amasty\Rules\Helper\Data::TYPE_CHEAPEST
 */
class Thecheapest extends AbstractRule
{
    const RULE_VERSION = '1.0.0';
    const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     *
     * @return Data
     * @throws \Exception
     */
    public function calculate($rule, $item, $qty)
    {
        $this->beforeCalculate($rule);
        $rulePercent = min(100, $rule->getDiscountAmount());
        $discountData = $this->_calculate($rule, $item, $rulePercent);
        $this->afterCalculate($discountData, $rule, $item);

        return $discountData;
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $rulePercent
     * @return Data Data
     */
    protected function _calculate($rule, $item, $rulePercent)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();
        $itemsId = $this->getAllowedItemsIds($item->getAddress(), $rule);

        if (in_array($item->getAmrulesId(), $itemsId)) {
            $itemQty = $this->getArrayValueCount($itemsId, $item->getAmrulesId());
            /** @var Data $discountData */
            $discountData = $this->calculateDiscount($item, $itemQty, $rulePercent);

            if (!$rule->getDiscountQty() || $rule->getDiscountQty() > $itemQty) {
                $discountPercent = min(100, $item->getDiscountPercent() + $rulePercent);
                $item->setDiscountPercent($discountPercent);
            }
        }

        return $discountData;
    }

    /**
     * @param Address $address
     * @param Rule $rule
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAllowedItemsIds($address, $rule)
    {
        $allItems = $this->getSortedItems($address, $rule, static::DEFAULT_SORT_ORDER);
        $sliceQty = $this->ruleQuantity(count($allItems), $rule);
        $allItems = array_slice($allItems, 0, $sliceQty);

        return $this->getItemsId($allItems);
    }

    /**
     * @param AbstractItem $item
     * @param float $itemQty
     * @param float $rulePercent
     *
     * @return Data
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */

    private function calculateDiscount($item, $itemQty, $rulePercent)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();

        $itemPrice = $this->rulesProductHelper->getItemPrice($item);
        $baseItemPrice = $this->rulesProductHelper->getItemBasePrice($item);
        $itemOriginalPrice = $this->rulesProductHelper->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->rulesProductHelper->getItemBaseOriginalPrice($item);

        $rulePct = $rulePercent / 100;
        $amount = $itemQty * $itemPrice * $rulePct;
        $baseOriginalAmount = $itemQty * $baseItemOriginalPrice * $rulePct;
        $baseAmount = $itemQty * $baseItemPrice * $rulePct;
        $originalAmount = $itemQty * $itemOriginalPrice * $rulePct;

        $discountData->setAmount($amount);
        $discountData->setBaseAmount($baseAmount);
        $discountData->setOriginalAmount($originalAmount);
        $discountData->setBaseOriginalAmount($baseOriginalAmount);

        return $discountData;
    }
}
