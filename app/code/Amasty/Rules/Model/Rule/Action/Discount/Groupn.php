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
 * Fixed Price: Each 5 items for $50
 *
 * @see \Amasty\Rules\Helper\Data::TYPE_GROUP_N
 */
class Groupn extends AbstractRule
{
    const RULE_VERSION = '1.0.0';

    const DEFAULT_SORT_ORDER = 'asc';

    public static $cachedDiscount = [];

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     *
     * @return Data
     *
     * @throws \Exception
     */
    public function calculate($rule, $item, $qty)
    {
        $this->beforeCalculate($rule);
        $discountData = $this->calculateDiscount($rule, $item);
        $this->afterCalculate($discountData, $rule, $item);

        return $discountData;
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     *
     * @return Data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function calculateDiscount($rule, $item)
    {
        $ruleId = $this->getRuleId($rule);

        if (!array_key_exists($ruleId, self::$cachedDiscount)) {
            $this->calculateDiscountForRule($item, $rule);
        }

        $discountData = isset(self::$cachedDiscount[$ruleId][$item->getId()])
            ? self::$cachedDiscount[$ruleId][$item->getId()]
            : $this->discountFactory->create();

        return $discountData;
    }

    /**
     * @param AbstractItem $item
     * @param Rule $rule
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function calculateDiscountForRule($item, $rule)
    {
        $allItems = $this->getSortedItems(
            $item->getAddress(),
            $rule,
            $this->getSortOrder($rule, self::DEFAULT_SORT_ORDER)
        );

        $totalPrice = $this->getItemsPrice($allItems);

        if ($totalPrice < $rule->getDiscountAmount()) {
            return $this;
        }

        $this->calculateDiscountForEachGroup($rule, $allItems, $totalPrice);

        return $this;
    }

    /**
     * @param Rule $rule
     * @param array $allItems
     * @param float $totalBasePrice
     */
    protected function calculateDiscountForEachGroup($rule, $allItems, $totalBasePrice)
    {
        $step = (int)$rule->getDiscountStep();
        $stepIteration = 0;
        if ($step == 0) {
            $step = 1;
        }

        while (count($allItems) >= $step) {
            $groupItems = array_slice($allItems, 0, $step);
            $groupItemsPrice = $this->getItemsPrice($groupItems);

            if ($groupItemsPrice < (float)$rule->getDiscountAmount()) {
                $firstItem = array_shift($allItems);
                unset($firstItem);
            } else {
                $stepIteration++;
                $this->calculateDiscountForItems(
                    $groupItemsPrice,
                    $rule,
                    $groupItems,
                    (float)$rule->getDiscountAmount()
                );
                $count = 0;

                foreach ($allItems as $i => $item) {
                    if ($count >= $step) {
                        break;
                    }

                    unset($allItems[$i]);
                    $count++;
                }
            }
        }

        $this->fixRoundingDiscount($rule, $totalBasePrice, $stepIteration);
    }

    /**
     * @param float $totalPrice
     * @param Rule $rule
     * @param AbstractItem[] $itemsForSet
     *
     * @param float $quoteAmount
     *
     * @throws \Exception
     */
    protected function calculateDiscountForItems($totalPrice, $rule, $itemsForSet, $quoteAmount)
    {
        $ruleId = $this->getRuleId($rule);

        foreach ($itemsForSet as $item) {
            if (isset(self::$cachedDiscount[$ruleId][$item->getId()])) {
                $discountData = self::$cachedDiscount[$ruleId][$item->getId()];
            } else {
                $discountData = $this->discountFactory->create();
            }

            $baseItemPrice = $this->rulesProductHelper->getItemBasePrice($item);
            $baseItemOriginalPrice = $this->rulesProductHelper->getItemBaseOriginalPrice($item);

            $percentage = $baseItemPrice / $totalPrice;
            $baseDiscount = $baseItemPrice - $quoteAmount * $percentage;
            $itemDiscount = $this->priceCurrency->convert($baseDiscount, $item->getQuote()->getStore());
            $baseOriginalDiscount = $baseItemOriginalPrice - $quoteAmount * $percentage;
            $originalDiscount = ($baseItemOriginalPrice / $baseItemPrice) *
                $this->priceCurrency->convert($baseOriginalDiscount, $item->getQuote()->getStore());

            $discountData->setAmount($itemDiscount + $discountData->getAmount());
            $discountData->setBaseAmount($baseDiscount + $discountData->getBaseAmount());
            $discountData->setOriginalAmount($originalDiscount + $discountData->getOriginalAmount());
            $discountData->setBaseOriginalAmount($baseOriginalDiscount + $discountData->getBaseOriginalAmount());

            self::$cachedDiscount[$ruleId][$item->getId()] = $discountData;
        }
    }

    /**
     * @param $items
     *
     * @return float|int
     */
    protected function getItemsPrice($items)
    {
        $totalPrice = 0;
        foreach ($items as $item) {
            $totalPrice += $this->validator->getItemBasePrice($item);
        }

        return $totalPrice;
    }

    /**
     * Fix rounding bug (floating 0.01) fir fixed discount
     *
     * @param Rule $rule
     * @param float $totalBasePrice
     * @param int $stepCount
     */
    protected function fixRoundingDiscount($rule, $totalBasePrice, $stepCount)
    {
        $ruleId = $this->getRuleId($rule);
        $appliedBaseDiscountTotal = 0;
        if (!isset(self::$cachedDiscount[$ruleId]) || !$stepCount) {
            return;
        }

        $quoteBaseAmount = (float)$rule->getDiscountAmount() * $stepCount;
        $quoteAmount = $this->priceCurrency->convertAndRound($quoteBaseAmount);

        foreach (self::$cachedDiscount[$ruleId] as $discountData) {
            $appliedBaseDiscountTotal += $this->priceCurrency->round($discountData->getBaseAmount());
        }

        $finalBasePrice = $totalBasePrice - $appliedBaseDiscountTotal;
        if ($finalBasePrice != $quoteBaseAmount) {
            $fix = $finalBasePrice - $quoteBaseAmount;
            $discountData->setBaseAmount($fix + $discountData->getBaseAmount());
        }

        $finalPrice = $this->priceCurrency->convertAndRound($finalBasePrice);

        if ($finalPrice != $quoteAmount) {
            $fix = $finalPrice - $quoteAmount;
            $discountData->setAmount($fix + $discountData->getAmount());
        }
    }
}
