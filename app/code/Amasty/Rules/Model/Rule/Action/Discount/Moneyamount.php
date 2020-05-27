<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model\Rule\Action\Discount;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;

/**
 * Amasty Rules calculation by action.
 *
 * @see \Amasty\Rules\Helper\Data::TYPE_AMOUNT
 */
class Moneyamount extends AbstractRule
{
    const RULE_VERSION = '1.0.0';
    const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     *
     * @return Data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function calculate($rule, $item, $qty)
    {
        $this->beforeCalculate($rule);
        $discountData = $this->_calculate($rule, $item);
        $this->afterCalculate($discountData, $rule, $item);

        return $discountData;
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     *
     * @return Data Data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _calculate($rule, $item)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();
        $allItems = $this->getSortedItems($item->getAddress(), $rule, self::DEFAULT_SORT_ORDER);
        $step = (int)$rule->getDiscountStep();
        $baseSum = 0;

        /** @var AbstractItem $allItem */
        foreach ($allItems as $allItem) {
            $baseSum += $this->validator->getItemBasePrice($allItem);
        }

        $timesToApply = floor($baseSum / max(1, $step));
        $maxTimesToApply = max(0, (int)$rule->getDiscountQty()); // remove negative values if any

        if ($maxTimesToApply) {
            $timesToApply = min($timesToApply, $maxTimesToApply);
        }

        $baseAmount = $timesToApply * $rule->getDiscountAmount();

        if ($baseAmount <= 0.001) {
            return $discountData;
        }

        $_rulePct = $baseAmount / $baseSum;
        $itemsId = $this->getItemsId($allItems);

        if (in_array($item->getAmrulesId(), $itemsId)) {
            $itemPrice = $this->validator->getItemPrice($item);
            $baseItemPrice = $this->validator->getItemBasePrice($item);
            $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
            $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);
            $itemQty = $this->getArrayValueCount($itemsId, $item->getAmrulesId());
            $discountData->setAmount($itemQty * $itemPrice * $_rulePct);
            $discountData->setBaseAmount($itemQty * $baseItemPrice * $_rulePct);
            $discountData->setOriginalAmount($itemQty * $itemOriginalPrice * $_rulePct);
            $discountData->setBaseOriginalAmount($itemQty * $baseItemOriginalPrice * $_rulePct);
        }

        return $discountData;
    }
}
