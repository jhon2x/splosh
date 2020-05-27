<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Block\Cart;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Amasty\Rules\Model\DiscountRegistry as DiscountRegistry;
use Amasty\Rules\Model\ConfigModel as ConfigModel;

/**
 * LayoutProcessor for discount breakdown.
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var DiscountRegistry
     */
    private $discountRegistry;

    /**
     * @var ConfigModel
     */
    private $configModel;

    public function __construct(
        DiscountRegistry $discountRegistry,
        ConfigModel $configModel
    ) {
        $this->discountRegistry = $discountRegistry;
        $this->configModel = $configModel;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if ($this->configModel->getShowDiscountBreakdown()) {
            $rulesWithDiscount = $this->discountRegistry->getRulesWithAmount();
            $rulesWithDiscountArray = $this->discountRegistry->convertRulesWithDiscountToArray($rulesWithDiscount);

            $jsLayout['components']['block-totals']['children']['before_grandtotal']['children']['discount-breakdown']
            ['config'] = [
                'amount' => $rulesWithDiscountArray
            ];
        }

        return $jsLayout;
    }
}
