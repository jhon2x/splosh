<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model\Rule\Action\Discount;

use Magento\Quote\Model\Quote\Item\AbstractItem as AbstractQuoteItem;
use Magento\SalesRule\Model\Rule\Action\Discount\Data as DiscountData;
use Magento\SalesRule\Model\Rule as RuleModel;

/**
 * Class AbstractSetof
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
abstract class AbstractSetof extends AbstractRule
{
    const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @var array
     */
    public static $cachedDiscount = [];

    /**
     * @var array|null
     */
    public static $allItems;

    /**
     * @param RuleModel $rule
     * @param AbstractQuoteItem $item
     * @param float $qty
     *
     * @return DiscountData Data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function calculate($rule, $item, $qty)
    {
        $this->beforeCalculate($rule);

        if (!isset(self::$allItems)) {
            self::$allItems = $this->getSortedItems($item->getAddress(), $rule, self::DEFAULT_SORT_ORDER);
        }

        $discountData = $this->calculateDiscount($rule, $item);
        $this->afterCalculate($discountData, $rule, $item);

        return $discountData;
    }

    /**
     * Calculate discount for rule once. Cached values is returned the next times
     *
     * @param RuleModel $rule
     * @param AbstractQuoteItem $item
     *
     * @return DiscountData
     *
     * @throws \Exception
     */
    protected function calculateDiscount($rule, $item)
    {
        $ruleId = $this->getRuleId($rule);

        if (!array_key_exists($ruleId, self::$cachedDiscount)) {
            $this->calculateDiscountForRule($rule, $item);
        }

        $discountData = isset(self::$cachedDiscount[$ruleId][$item->getProductId()])
            ? self::$cachedDiscount[$ruleId][$item->getProductId()]
            : $this->discountFactory->create();

        return $discountData;
    }

    /**
     * Realize this function to calculate discount.
     *
     * @param RuleModel $rule
     * @param AbstractQuoteItem $item
     *
     * @return mixed
     */
    abstract protected function calculateDiscountForRule($rule, $item);

    /**
     * Determining the elements that will make up the set and the number of sets
     *
     * @param RuleModel $rule
     *
     * @return array|null
     */
    protected function prepareDataForCalculation($rule)
    {
        $promoSkus = $rule->getAmrulesRule()->getPromoSkus();
        $promoCategories = $rule->getAmrulesRule()->getPromoCats();

        if ($promoSkus || $promoCategories) {
            list($qtySkus, $itemsForSet) = $this->getItemsForSet($rule);
            $setQty = $qtySkus ? min($qtySkus) : 0;

            if ($rule->getDiscountQty() != null) {
                $setQty = min($setQty, (int)$rule->getDiscountQty());
            }

            if ($setQty > 0) {
                $itemsForSet = $this->removeExcessItems($itemsForSet, $qtySkus, $setQty);

                return [
                    $setQty,
                    $itemsForSet
                ];
            }
        }

        return null;
    }

    /**
     * @param array $itemsForSet
     * @param array $qtySkus
     * @param int $setQty
     *
     * @return array
     */
    private function removeExcessItems($itemsForSet, $qtySkus, $setQty)
    {
        foreach ($itemsForSet as $i => $item) {
            if ($qtySkus[$item->getSku()] > $setQty) {
                $qtySkus[$item->getSku()]--;
                unset($itemsForSet[$i]);
            }
        }

        return $itemsForSet;
    }

    /**
     * Determining the elements that will make up the set
     *
     * @param RuleModel $rule
     *
     * @return array
     */
    protected function getItemsForSet($rule)
    {
        $qtySkus = [];
        $itemsForSet = self::$allItems ?: [];
        $skus = $this->rulesDataHelper->getRuleSkus($rule);

        foreach ($skus as $sku) {
            $qtySkus[$sku] = 0;
        }

        foreach ($itemsForSet as $i => $item) {
            if (in_array($item->getSku(), $skus)) {
                $qtySkus[$item->getSku()]++;
            } else {
                unset($itemsForSet[$i]);
            }
        }

        $categories = $this->rulesDataHelper->getRuleCats($rule);
        if (!$categories) {
            return [
                $qtySkus,
                $itemsForSet
            ];
        }

        if ($arrayForCategoriesSet = array_diff_key(self::$allItems ?: [], $itemsForSet)) {
            $qtyCategories = $this->formCategorySet($categories, $arrayForCategoriesSet);

            if ($qtyCategories) {
                $qtySkus += $qtyCategories;
                $itemsForSet = self::$allItems;

                foreach ($itemsForSet as $i => $item) {
                    if (!array_key_exists($item->getSku(), $qtySkus)) {
                        unset($itemsForSet[$i]);
                    }
                }
            } else {
                $qtySkus = [];
            }
        } else {
            $qtySkus = [];
        }

        return [$qtySkus, $itemsForSet];
    }

    /**
     * @param array $categories
     * @param array $itemsForSet
     *
     * @return array
     */
    protected function formCategorySet($categories, $itemsForSet)
    {
        $categoriesMatrix = $this->getCategoriesMatrix($categories, $itemsForSet);

        return $this->checkRows($categoriesMatrix);
    }

    /**
     * Initialize category matrix where columns are categories, rows are items from cart
     *
     * @param array $categories
     * @param array $itemsForSet
     *
     * @return array
     */
    private function getCategoriesMatrix($categories, $itemsForSet)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $catsCollection */
        $catsCollection = $this->categoriesCollection->create();
        $catsCollection->addIdFilter($categories);
        $catsCollection->setOrder('level', $catsCollection::SORT_ORDER_DESC);

        $catsMatrix = [];
        $categoriesArray = $catsCollection->getData();

        foreach ($categoriesArray as $item) {
            $catsMatrix[$item['entity_id']] = [];
        }

        foreach ($itemsForSet as $item) {
            $productCategories = $item->getProduct()->getCategoryIds();

            foreach ($productCategories as $category) {
                $category = (int) $category;

                if (isset($catsMatrix[$category][$item->getSku()])) {
                    $catsMatrix[$category][$item->getSku()]++;
                    continue;
                }

                if (isset($catsMatrix[$category])) {
                    $catsMatrix[$category][$item->getSku()] = 1;
                }
            }
        }

        return $catsMatrix;
    }

    /**
     * @param array $categoriesMatrix
     * @param array $itemsForSet
     *
     * @return array
     */
    private function checkRows($categoriesMatrix, $itemsForSet = [])
    {
        /** Check if exist category with one item */
        foreach ($categoriesMatrix as $categoryId => $items) {
            if (is_array($items) && $items) {
                //@codingStandardsIgnoreStart
                if (count($items) == 1) {
                    return $this->moveItemFromMatrixToSet($items, $categoriesMatrix, $categoryId, $itemsForSet);
                }
                //@codingStandardsIgnoreEnd
            } else {
                return [];
            }
        }

        foreach ($categoriesMatrix as $categoryId => $items) {
            if (is_array($items) && $items) {
                return $this->moveItemFromMatrixToSet($items, $categoriesMatrix, $categoryId, $itemsForSet);
            } else {
                return [];
            }
        }

        return $itemsForSet;
    }

    /**
     * @param array $items
     * @param array $categoriesMatrix
     * @param int|string $categoryId
     * @param array $itemsForSet
     *
     * @return array|mixed
     */
    private function moveItemFromMatrixToSet($items, $categoriesMatrix, $categoryId, $itemsForSet)
    {
        $sku = '';

        /** Get the cheapest item from category */
        foreach ($items as $key => $value) {
            $itemsForSet[$key] = $value;
            $sku = $key;
            unset($categoriesMatrix[$categoryId]);
            break;
        }

        /** remove this item from another categories */
        foreach ($categoriesMatrix as $key => $value) {
            if (isset($categoriesMatrix[$key][$sku])) {
                unset($categoriesMatrix[$key][$sku]);
            }
        }

        return $this->checkRows($categoriesMatrix, $itemsForSet);
    }
}
