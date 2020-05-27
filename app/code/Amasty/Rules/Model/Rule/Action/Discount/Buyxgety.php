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

/**
 * Base class for buyXgetY action group.
 *
 * @see \Amasty\Rules\Helper\Data::BUY_X_GET_Y
 */
abstract class Buyxgety extends AbstractRule
{
    const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @var array
     */
    protected $passedItems = [];

    /**
     * @param Address $address
     * @param Rule $rule
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTriggerElements($address, $rule)
    {
        // find all X (trigger) elements
        $triggerItems = [];
        foreach ($this->getSortedItems($address, $rule, self::DEFAULT_SORT_ORDER) as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            if (!$item->getAmrulesId()) {
                continue;
            }

            $promoSku  = $this->rulesDataHelper->getRuleSkus($rule);

            if (in_array($item->getSku(), $promoSku)) {
                continue;
            }

            if (!$promoSku) {
                $itemCats = $item->getCategoryIds();

                if (!$itemCats) {
                    $itemCats = $item->getProduct()->getCategoryIds();
                }
                $promoCats = $this->rulesDataHelper->getRuleCats($rule);

                if ($itemCats !== null && array_intersect($promoCats, $itemCats)) {
                    continue;
                }
            }
            $triggerItems[$item->getAmrulesId()] = $item;
        }

        return $triggerItems;
    }

    /**
     * @param array $triggerItems
     *
     * @return int
     */
    public function getTriggerElementQty($triggerItems)
    {
        $realQty = 0;

        /** @var AbstractItem $item */
        foreach ($triggerItems as $item) {
            $realQty += $this->getItemQty($item);
        }

        return $realQty;
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     *
     * @return bool
     */
    public function isDiscountedItem($rule, $item)
    {
        $product = $item->getProduct();
        // for configurable product we need to use the child
        if ($item->getHasChildren() && $item->getProductType() == 'configurable') {
            foreach ($item->getChildren() as $child) {
                // one iteration only
                $product = $child->getProduct();
            }
        }

        $cats = $this->rulesDataHelper->getRuleCats($rule);
        $sku  = $this->rulesDataHelper->getRuleSkus($rule);

        $currentSku  = $product->getSku();
        $currentCats = $product->getCategoryIds();

        $parent = $item->getParentItem();

        if (isset($parent)) {
            $parentType = $parent->getProductType();
            if ($parentType == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                $currentSku  = $item->getParentItem()->getProduct()->getSku();
                $currentCats = $item->getParentItem()->getProduct()->getCategoryIds();
            }
        }

        if (!in_array($currentSku, $sku) && !array_intersect($cats, $currentCats)) {
            return false;
        }

        return true;
    }

    /**
     * @param AbstractItem $item
     * @param array $triggerItems
     * @param array $passed
     *
     * @return bool
     */
    public function canProcessItem($item, $triggerItems, $passed)
    {
        if (!$item->getAmrulesId()) {
            return false;
        }
        //do not apply discont on triggers
        if (isset($triggerItems[$item->getAmrulesId()])) {
            return false;
        }

        if (in_array($item->getAmrulesId(), $passed)) {
            return false;
        }

        return true;
    }

    /**
     * @param Rule $rule
     * @param int|float $realQty
     *
     * @return float|int|mixed
     */
    protected function getNQty($rule, $realQty)
    {
        if ($rule->getDiscountStep() > $realQty) {
            return 0;
        } else {
            $step = $rule->getDiscountStep();
            $step = max(1, $step);
            $dataNqty = $rule->getAmrulesRule()->getData('nqty');
            $count = floor($realQty / $step);

            if ($dataNqty) {
                $count *= $dataNqty;
            }

            $discountQty = $rule->getDiscountQty();

            if ($discountQty) {
                $nqty = min($count, $discountQty);
            } else {
                $nqty = $count;
            }

            if ($nqty <= 0) {
                $nqty = 1;
            }

            return $nqty;
        }
    }
}
