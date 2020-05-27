<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin\Condition;

use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\Rule;

/**
 * Additional validation for rules with buyxget actions,
 */
class Combine
{
    /**
     * @var \Amasty\Rules\Helper\Data
     */
    private $rulesDataHelper;

    /**
     * @var \Amasty\Rules\Model\RuleResolver
     */
    private $ruleResolver;

    public function __construct(
        \Amasty\Rules\Helper\Data $rulesDataHelper,
        \Amasty\Rules\Model\RuleResolver $ruleResolver
    ) {
        $this->rulesDataHelper = $rulesDataHelper;
        $this->ruleResolver = $ruleResolver;
    }

    public function aroundValidate(
        \Magento\Rule\Model\Condition\Combine $subject,
        \Closure $proceed,
        $type
    ) {

        if ($type instanceof Item) {
            $discountItem = $this->checkActionItem($subject->getRule(), $type);
            if ($discountItem) {
                return true;
            }
        }

        return $proceed($type);
    }

    /**
     * @param Rule $rule
     * @param Item $item
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function checkActionItem($rule, $item)
    {
        $action = $rule->getSimpleAction();

        if (strpos($action, "buyxget") !== false) {
            $this->ruleResolver->getSpecialPromotions($rule);

            $promoCats = $this->rulesDataHelper->getRuleCats($rule);
            $promoSku  = $this->rulesDataHelper->getRuleSkus($rule);
            $itemSku   = $item->getSku();
            $itemCats  = $item->getCategoryIds();

            if (!$itemCats) {
                $itemCats = $item->getProduct()->getCategoryIds();
            }

            $parent = $item->getParentItem();

            if ($parent) {
                $parentType = $parent->getProductType();
                if ($parentType == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                    $itemSku  = $item->getParentItem()->getProduct()->getSku();
                    $itemCats = $item->getParentItem()->getProduct()->getCategoryIds();
                }
            }

            if (in_array($itemSku, $promoSku)) {
                return true;
            }

            if ($itemCats !== null && array_intersect($promoCats, $itemCats)) {
                return true;
            }
        }

        return false;
    }
}
