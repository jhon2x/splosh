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
 * @see \Amasty\Rules\Helper\Data::TYPE_GROUP_N_DISC
 */
class GroupnDisc extends AbstractRule
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
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _calculate($rule, $item)
    {
        $discountData = $this->discountFactory->create();
        $allItems = $this->getSortedItems(
            $item->getAddress(),
            $rule,
            $this->getSortOrder($rule, self::DEFAULT_SORT_ORDER)
        );

        $qty = $this->ruleQuantity(count($allItems), $rule);

        if (!$this->hasDiscountItems($allItems, $qty)) {
            return $discountData;
        }

        $currQty = 0;
        $lastId = -1;
        $percentage = 0;
        $originalDiscount = 0;
        $baseItemOriginalDiscount = 0;
        $result = [];
        $step = (int)$rule->getDiscountStep();

        if ($step === 0) {
            $step = 1;
        }

        $countPrices = count($allItems);
        //we must check all items price and compare with group price
        $totalPrice = 0;

        foreach ($allItems as $allItem) {
            $totalPrice +=  $this->validator->getItemBasePrice($allItem);
        }

        if ($totalPrice < $rule->getDiscountAmount()) {
            return $discountData;
        }

        foreach ($allItems as $i => $allItem) {
            if ($this->skipBySteps($rule, $step, $i, $currQty, $qty)) {
                continue;
            }
            ++$currQty;
            $itemPrice = $this->rulesProductHelper->getItemPrice($allItem);
            $baseItemPrice = $this->rulesProductHelper->getItemBasePrice($allItem);
            $itemOriginalPrice = $this->rulesProductHelper->getItemOriginalPrice($allItem);
            $baseItemOriginalPrice = $this->rulesProductHelper->getItemBaseOriginalPrice($allItem);

            if ($i < $countPrices - ($countPrices % $step)) {
                $discount = $itemPrice * $rule->getDiscountAmount() / 100;
                $originalDiscount = $itemOriginalPrice * $rule->getDiscountAmount() / 100;
                $baseDiscount = $baseItemPrice * $rule->getDiscountAmount() / 100;
                $baseItemOriginalDiscount = $baseItemOriginalPrice * $rule->getDiscountAmount() / 100;
                $percentage = $discount * 100 / $itemPrice;
            } else {
                $discount = 0;
                $baseDiscount = 0;
            }

            if ($allItem->getAmrulesId() != $lastId) {
                $lastId = (int) $allItem->getAmrulesId();
                $result[$lastId] = [];
                $result[$lastId]['discount'] = $discount;
                $result[$lastId]['base_discount'] = $baseDiscount;
                $result[$lastId]['percent'] = $percentage;
                $result[$lastId]['price'] = $itemPrice;
                $result[$lastId]['base_price'] = $baseItemPrice;
                $result[$lastId]['original_discount'] = $originalDiscount;
                $result[$lastId]['base_item_original_discount'] = $baseItemOriginalDiscount;
            } else {
                $result[$lastId]['discount'] += $discount;
                $result[$lastId]['original_discount'] += $originalDiscount;
                $result[$lastId]['base_item_original_discount'] += $baseItemOriginalDiscount;
                $result[$lastId]['base_discount'] += $baseDiscount;
                $result[$lastId]['percent'] = $percentage;
                $result[$lastId]['price'] = $itemPrice;
                $result[$lastId]['base_price'] = $baseItemPrice;
            }
        }

        $result= $this->spreadDiscount($result);

        if (isset($result[$item->getAmrulesId()])) {
            $discountData->setAmount($result[$item->getAmrulesId()]['discount']);
            $discountData->setBaseAmount($result[$item->getAmrulesId()]['base_discount']);
            $discountData->setOriginalAmount($result[$item->getAmrulesId()]['original_discount']);
            $discountData->setBaseOriginalAmount($result[$item->getAmrulesId()]['base_item_original_discount']);
        }

        return $discountData;
    }

    /**
     * @param array $discount
     *
     * @return array
     */
    protected function spreadDiscount($discount)
    {
        $negativeDiscount = 0;
        $negativeBaseDiscount = 0;
        $discountSum = 0;
        $result = [];

        foreach ($discount as $key => $rule) {
            $discountSum += $rule['discount'];
            if ($rule['discount']<0) {
                $negativeDiscount += abs($rule['discount']);
                $negativeBaseDiscount +=abs($rule['base_discount']);
            } else {
                $result[$key] = $rule;
            }
        }

        if ($discountSum < 0) {
            return [];
        }

        if ($negativeDiscount > 0) {
            foreach ($result as $key => $res) {
                if (($res['discount']-$negativeDiscount)<0) {
                    $result[$key]['discount'] = 0;
                    $result[$key]['original_discount'] = 0;
                    $result[$key]['percent'] = 0;
                    $negativeDiscount -= $res['discount'];
                } else {
                    $result[$key]['discount'] -=$negativeDiscount;
                    $result[$key]['original_discount'] -= $negativeDiscount;
                    $result[$key]['percent'] = $result[$key]['discount'] * 100 / $res['price'];
                    $negativeDiscount = 0;
                }

                if (($res['base_discount']-$negativeBaseDiscount)<0) {
                    $result[$key]['base_discount'] = 0;
                    $result[$key]['base_item_original_discount'] = 0;
                    $negativeDiscount -= $res['base_discount'];
                } else {
                    $result[$key]['base_discount'] -=$negativeBaseDiscount;
                    $result[$key]['base_item_original_discount'] -= $negativeBaseDiscount;
                    $negativeBaseDiscount = 0;
                }
            }
        }

        return $result;
    }
}
