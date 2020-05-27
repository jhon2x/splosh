<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Test\Integration\Model\Discount;

/**
 * @codingStandardsIgnoreFile
 * @codeCoverageIgnore
 */
class BuygetnPercTest extends BaseDiscount
{
    /**
     * @var string
     */
    protected $testProductSku = 'simple3';

    /**
     * @var array
     */
    protected $resultCompareDiscountArray = [
        'amount' => 3,
        'baseAmount' => 3,
        'originalAmount' => 3,
        'baseOriginalAmount' => 3
    ];

    /**
     * @var string
     */
    protected $fixtureRule = 'cart_rule_buy_x_get_y_10_percent_discount';

    /**
     * @magentoDataFixture ../../../../app/code/Amasty/Rules/Test/Integration/_files/quote_with_simple_products.php
     * @magentoDataFixture ../../../../app/code/Amasty/Rules/Test/Integration/_files/cart_rule_buy_x_get_y_10_percent_discount.php
     */
    public function testCalculateDiscount()
    {
        $this->baseTest();
    }

    /**
     * {@inheritdoc}
     */
    protected function initObjectRule()
    {
        $this->object = $this->objectManager->create(
            \Amasty\Rules\Model\Rule\Action\Discount\BuyxgetnPerc::class
        );

        return $this;
    }

    /**
     * @param $salesRuleId
     */
    protected function prepareAmastyRulesData($salesRuleId)
    {
        /** @var \Amasty\Rules\Model\Rule $amastyRuleModel */
        $amastyRuleModel = $this->objectManager->create(\Amasty\Rules\Model\Rule::class);
        $amastyRuleModel
            ->setData('salesrule_id', $this->salesRule->getRuleId())
            ->setPriceselector(0)
            ->setPromoSkus('simple3')
            ->setNqty(1)
            ->setSkipRule(0)
            ->save();

        $this->salesRule->setData('amrules_rule', $amastyRuleModel);
    }
}
