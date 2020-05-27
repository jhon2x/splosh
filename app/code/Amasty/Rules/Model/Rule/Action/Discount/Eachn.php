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
 * Base class for 'eachN; action group.
 * \Amasty\Rules\Helper\Data::TYPE_EACH_M_AFT_N and others.
 */
abstract class Eachn extends AbstractRule
{
    const RULE_VERSION = '1.0.0';
    const DEFAULT_SORT_ORDER = 'desc';
    const USE_FOR_SAME_PRODUCT = 1;

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
     * @codingStandardsIgnoreStart
     *
     * @param Rule $rule
     * @param AbstractItem $item
     *
     * @return Data
     */
    abstract protected function _calculate($rule, $item);
    //@codingStandardsIgnoreEnd

    /**
     * @param array $allItems
     * @param Rule $rule
     *
     * @return array
     */
    public function reduceItems($allItems, $rule)
    {
        $discountStep = (int)$rule->getDiscountStep();
        $step = $discountStep !== '' ? $discountStep : (int)$rule->getAmrulesRule()->getEachm();

        if ($step <= 0) {
            $step = 1;
        }

        $groupedItems = $this->groupItemsBySku($allItems);
        $reducedItems = [];
        foreach ($groupedItems as $group) {
            //@codingStandardsIgnoreStart
            $count = count($group);
            //@codingStandardsIgnoreEnd
            $group = array_slice($group, $count % $step);
            $reducedItemsByGroup = array_values($group);
            $reducedItems += $reducedItemsByGroup;
        }

        return $reducedItems;
    }

    /**
     * @param AbstractItem[] $allItems
     *
     * @return array
     */
    private function groupItemsBySku($allItems)
    {
        $groupedItems = [];
        foreach ($allItems as $item) {
            $groupedItems[$item->getSku()][] = $item;
        }

        return $groupedItems;
    }
}
