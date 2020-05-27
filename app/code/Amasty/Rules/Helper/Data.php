<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */

namespace Amasty\Rules\Helper;

/**
 * Keeper of action types.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const TYPE_CHEAPEST = 'thecheapest';//+
    const TYPE_EXPENCIVE = 'themostexpencive';//+
    const TYPE_AMOUNT = 'moneyamount';//+

    const TYPE_EACH_N = 'eachn_perc';//+
    const TYPE_EACH_N_FIXDISC = 'eachn_fixdisc';//+
    const TYPE_EACH_N_FIXED = 'eachn_fixprice';//+

    const TYPE_EACH_M_AFT_N_PERC = 'eachmaftn_perc';//+
    const TYPE_EACH_M_AFT_N_DISC = 'eachmaftn_fixdisc';//+
    const TYPE_EACH_M_AFT_N_FIX = 'eachmaftn_fixprice';//+

    const TYPE_GROUP_N = 'groupn';
    const TYPE_GROUP_N_DISC = 'groupn_disc';

    const TYPE_XY_PERCENT = 'buyxgety_perc';
    const TYPE_XY_FIXED = 'buyxgety_fixprice';
    const TYPE_XY_FIXDISC = 'buyxgety_fixdisc';

    const TYPE_XN_PERCENT = 'buyxgetn_perc';
    const TYPE_XN_FIXED = 'buyxgetn_fixprice';
    const TYPE_XN_FIXDISC = 'buyxgetn_fixdisc';

    const TYPE_AFTER_N_FIXED = 'aftern_fixprice';
    const TYPE_AFTER_N_DISC = 'aftern_disc';
    const TYPE_AFTER_N_FIXDISC = 'aftern_fixdisc';

    const TYPE_SETOF_PERCENT = 'setof_percent';
    const TYPE_SETOF_FIXED = 'setof_fixed';

    const BUY_X_GET_Y = [
        self::TYPE_XN_PERCENT,
        self::TYPE_XN_FIXED,
        self::TYPE_XN_FIXDISC
    ];

    const TYPE_EACH_M_AFT_N = [
        self::TYPE_EACH_M_AFT_N_PERC,
        self::TYPE_EACH_M_AFT_N_DISC,
        self::TYPE_EACH_M_AFT_N_FIX
    ];

    const GROUP_EACH_N = [
        self::TYPE_EACH_N,
        self::TYPE_EACH_N_FIXED,
        self::TYPE_EACH_N_FIXDISC,
    ];

    protected $passedItems = [];

    /**
     * @param int $itemId
     */
    public function addPassedItem($itemId)
    {
        $this->passedItems[] = $itemId;
    }

    /**
     * @return array
     */
    public function getPassedItems()
    {
        return $this->passedItems;
    }

    /**
     * @param bool $asOptions
     *
     * @return array
     */
    public function getDiscountTypes($asOptions = false)
    {
        $types = [
            self::TYPE_CHEAPEST => __('The Cheapest, also for Buy 1 get 1 free'),
            self::TYPE_EXPENCIVE => __('The Most Expensive'),
            self::TYPE_AMOUNT => __('Get $Y for each $X spent'),

            self::TYPE_EACH_N => __('Percent Discount: each 2-d, 4-th, 6-th with 15% 0ff'),
            self::TYPE_EACH_N_FIXDISC => __('Fixed Discount: each 3-d, 6-th, 9-th with $15 0ff'),
            self::TYPE_EACH_N_FIXED => __('Fixed Price: each 5th, 10th, 15th for $49'),

            self::TYPE_EACH_M_AFT_N_PERC =>
                __('Percent Discount: each 1st, 3rd, 5th with 15% 0ff after 5 items added to the cart'),
            self::TYPE_EACH_M_AFT_N_DISC =>
                __('Fixed Discount: each 3d, 7th, 11th with $15 0ff after 5 items added to the cart'),
            self::TYPE_EACH_M_AFT_N_FIX =>
                __('Fixed Price: each 5th, 7th, 9th for $89.99 after 5 items added to the cart'),

            self::TYPE_GROUP_N => __('Fixed Price: Each 5 items for $50'),
            self::TYPE_GROUP_N_DISC => __('Percent Discount: Each 5 items with 10% off'),

            self::TYPE_XN_PERCENT => __('Percent Discount: Buy X get Y Free'),
            self::TYPE_XN_FIXDISC => __('Fixed Discount:  Buy X get Y with $10 Off'),
            self::TYPE_XN_FIXED => __('Fixed Price: Buy X get Y for $9.99'),

            self::TYPE_AFTER_N_DISC => __('Percent Discount'),
            self::TYPE_AFTER_N_FIXDISC => __('Fixed Discount'),
            self::TYPE_AFTER_N_FIXED => __('Fixed Price'),

            self::TYPE_SETOF_PERCENT => __('Percent discount for product set'),
            self::TYPE_SETOF_FIXED => __('Fixed price for product set'),

        ];

        if (!$asOptions) {
            $groups = [
                'Popular' => [
                    self::TYPE_CHEAPEST,
                    self::TYPE_EXPENCIVE,
                    self::TYPE_AMOUNT
                ],
                'Buy X Get Y (X and Y are different products)' => [
                    self::TYPE_XN_PERCENT,
                    self::TYPE_XN_FIXDISC,
                    self::TYPE_XN_FIXED
                ],
                'Each N-th' => [
                    self::TYPE_EACH_N,
                    self::TYPE_EACH_N_FIXDISC,
                    self::TYPE_EACH_N_FIXED
                ],
                'Each Product After N' => [
                    self::TYPE_EACH_M_AFT_N_PERC,
                    self::TYPE_EACH_M_AFT_N_DISC,
                    self::TYPE_EACH_M_AFT_N_FIX
                ],
                'Each Group of N' => [
                    self::TYPE_GROUP_N,
                    self::TYPE_GROUP_N_DISC
                ],
                'Product Set' => [
                    self::TYPE_SETOF_PERCENT,
                    self::TYPE_SETOF_FIXED
                ],
            ];

            $result = [];

            foreach ($groups as $groupName => $groupActions) {
                $values = [];
                foreach ($groupActions as $k) {
                    $values[] = [
                        'value' => $k,
                        'label' => $types[$k],
                    ];
                }
                $result[] = [
                    'label' => __($groupName),
                    'value' => $values,
                ];
            }
            $types = $result;
        }

        return $types;
    }

    /**
     * @param string $rule
     *
     * @return mixed|string
     */
    public function getFilePath($rule)
    {
        $rule = implode('_', array_map('ucfirst', explode('_', $rule)));
        $rule = str_replace('_', '', $rule);
        $rule = 'Amasty\Rules\Model\Rule\Action\Discount\\' . $rule;

        return $rule;
    }

    /**
     * @param string $created
     *
     * @return float
     */
    public function getMembership($created)
    {
        $time = round((time() - strtotime($created)) / 60 / 60 / 24);

        return $time;
    }

    /**
     * @codingStandardsIgnoreStart
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    public static function comparePrices($a, $b)
    {
        $res = ($a['price'] < $b['price']) ? -1 : 1;
        if ($a['price'] == $b['price']) {
            $res = ($a['id'] < $b['id']) ? -1 : 1;
            if ($a['id'] == $b['id']) {
                $res = 0;
            }
        }

        return $res;
    }
    //@codingStandardsIgnoreEnd

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     *
     * @return array
     */
    public function getRuleCats($rule)
    {
        $promoCats = explode(',', $rule->getAmrulesRule()->getPromoCats());
        $promoCats = array_map('trim', $promoCats);
        $promoCats = array_filter($promoCats);

        return $promoCats;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     *
     * @return array
     */
    public function getRuleSkus($rule)
    {
        $promoSku = explode(',', $rule->getAmrulesRule()->getPromoSkus());
        $promoSku = array_map('trim', $promoSku);
        $promoSku = array_filter($promoSku);

        return $promoSku;
    }

    /**
     * @codingStandardsIgnoreStart
     *
     * @return array
     */
    public static function staticGetDiscountTypes()
    {
        $types = [
            self::TYPE_CHEAPEST => __('The Cheapest, also for Buy 1 get 1 free'),
            self::TYPE_EXPENCIVE => __('The Most Expensive'),
            self::TYPE_AMOUNT => __('Get $Y for each $X spent'),

            self::TYPE_EACH_N => __('Percent Discount: each 2-d, 4-th, 6-th with 15% 0ff'),
            self::TYPE_EACH_N_FIXDISC => __('Fixed Discount: each 3-d, 6-th, 9-th with $15 0ff'),
            self::TYPE_EACH_N_FIXED => __('Fixed Price: each 5th, 10th, 15th for $49'),

            self::TYPE_EACH_M_AFT_N_PERC =>
                __('Percent Discount: each 1st, 3rd, 5th with 15% 0ff after 5 items added to the cart'),
            self::TYPE_EACH_M_AFT_N_DISC =>
                __('Fixed Discount: each 3d, 7th, 11th with $15 0ff after 5 items added to the cart'),
            self::TYPE_EACH_M_AFT_N_FIX =>
                __('Fixed Price: each 5th, 7th, 9th for $89.99 after 5 items added to the cart'),

            self::TYPE_GROUP_N => __('Fixed Price: Each 5 items for $50'),
            self::TYPE_GROUP_N_DISC => __('Percent Discount: Each 5 items with 10% off'),

            self::TYPE_XY_PERCENT => __('Percent Discount: Buy X get Y Free'),
            self::TYPE_XY_FIXDISC => __('Fixed Discount:  Buy X get Y with $10 Off'),
            self::TYPE_XY_FIXED => __('Fixed Price: Buy X get Y for $9.99'),

            self::TYPE_XN_PERCENT => __('Percent Discount: Buy X get N Free'),
            self::TYPE_XN_FIXDISC => __('Fixed Discount:  Buy X get N with $10 Off'),
            self::TYPE_XN_FIXED => __('Fixed Price: Buy X get N for $9.99'),

            self::TYPE_AFTER_N_DISC => __('Percent Discount'),
            self::TYPE_AFTER_N_FIXDISC => __('Fixed Discount'),
            self::TYPE_AFTER_N_FIXED => __('Fixed Price'),

            self::TYPE_SETOF_PERCENT => __('Percent discount for product set'),
            self::TYPE_SETOF_FIXED => __('Fixed price for product set'),

        ];

        return $types;
    }
    //@codingStandardsIgnoreEnd
}
