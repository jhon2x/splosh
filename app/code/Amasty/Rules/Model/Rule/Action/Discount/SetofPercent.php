<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Rule as RuleModel;

/**
 * Amasty Rules calculation by action.
 * @see \Amasty\Rules\Helper\Data::TYPE_SETOF_PERCENT
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class SetofPercent extends AbstractSetof
{
    /**
     * @param RuleModel $rule
     *
     * @return $this
     */
    protected function calculateDiscountForRule($rule, $item)
    {
        list($setQty, $itemsForSet) = $this->prepareDataForCalculation($rule);

        if (!$itemsForSet) {
            return $this;
        }

        $this->calculateDiscountForItems($rule, $itemsForSet);

        foreach ($itemsForSet as $i => $item) {
            unset(self::$allItems[$i]);
        }

        return $this;
    }

    /**
     * @param RuleModel $rule
     * @param array $itemsForSet
     *
     * @return void
     *
     * @throws \Exception
     */
    private function calculateDiscountForItems($rule, $itemsForSet)
    {
        $ruleId = $this->getRuleId($rule);

        foreach ($itemsForSet as $item) {
            /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
            $discountData = $this->discountFactory->create();

            $baseItemPrice = $this->rulesProductHelper->getItemBasePrice($item);
            $baseItemOriginalPrice = $this->rulesProductHelper->getItemBaseOriginalPrice($item);

            $percentage = min(100, $rule->getDiscountAmount()) / 100;
            $baseDiscount = $baseItemPrice * $percentage;
            $itemDiscount = $this->priceCurrency->convert($baseDiscount, $item->getQuote()->getStore());
            $baseOriginalDiscount = $baseItemOriginalPrice * $percentage;
            $originalDiscount = $this->priceCurrency->convert($baseOriginalDiscount, $item->getQuote()->getStore());

            if (!isset(self::$cachedDiscount[$ruleId][$item->getProductId()])) {
                $discountData->setAmount($itemDiscount);
                $discountData->setBaseAmount($baseDiscount);
                $discountData->setOriginalAmount($originalDiscount);
                $discountData->setBaseOriginalAmount($baseOriginalDiscount);
            } else {
                /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $cachedItem */
                $cachedItem = self::$cachedDiscount[$ruleId][$item->getProductId()];
                $discountData->setAmount($itemDiscount + $cachedItem->getAmount());
                $discountData->setBaseAmount($baseDiscount + $cachedItem->getBaseAmount());
                $discountData->setOriginalAmount($originalDiscount + $cachedItem->getOriginalAmount());
                $discountData->setBaseOriginalAmount($baseOriginalDiscount + $cachedItem->getBaseOriginalAmount());
            }

            self::$cachedDiscount[$ruleId][$item->getProductId()] = $discountData;
        }
    }
}
