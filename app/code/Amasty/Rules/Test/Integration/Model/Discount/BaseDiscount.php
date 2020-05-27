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
class BaseDiscount extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Amasty\Rules\Model\Rule\Action\Discount\BuyxgetnFixdisc
     */
    protected $object;

    protected $objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var int|string
     */
    protected $currentTestedRuleId;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $salesRule;

    /**
     * @var array
     */
    protected $resultCompareDiscountArray = [];

    /**
     * @var string
     */
    protected $testProductSku = '';

    /**
     * A Fixture for some rule
     *
     * @var string
     */
    protected $fixtureRule = 'cart_rule_buy_x_gey_y_30_discount_amount';

    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $this->currentTestedRuleId = $this->registry
            ->registry('Magento/SalesRule/_files/' . $this->fixtureRule);
        $this->salesRule = $this->objectManager->create(\Magento\SalesRule\Model\Rule::class)
            ->load($this->currentTestedRuleId);
        $this->initObjectRule();
        $this->prepareAmastyRulesData($this->currentTestedRuleId);
    }

    /**
     *  Rule's object inits
     *
     * @return $this
     */
    protected function initObjectRule()
    {
        return $this;
    }

    /**
     * baseTest
     */
    protected function baseTest()
    {
        /** @var $session \Magento\Checkout\Model\Session  */
        $session = $this->objectManager->create(\Magento\Checkout\Model\Session::class);
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get($this->testProductSku);
        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), $product->getId());
        $quoteItem->setOriginalPrice($product->getPrice());
        $quoteItem->setBaseOriginalPrice($product->getPrice());
        $discountData = $this->object->calculate($this->salesRule, $quoteItem, 1);

        $resultArray = [
            'amount' => $discountData->getAmount(),
            'baseAmount' => $discountData->getBaseAmount(),
            'originalAmount' => $discountData->getOriginalAmount(),
            'baseOriginalAmount' => $discountData->getBaseOriginalAmount()
        ];

        $this->assertEquals($this->resultCompareDiscountArray, $resultArray);
    }

    /**
     * Gets \Magento\Quote\Model\Quote\Item from \Magento\Quote\Model\Quote by product id
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param mixed $productId
     * @return \Magento\Quote\Model\Quote\Item|null
     */
    protected function _getQuoteItemIdByProductId(\Magento\Quote\Model\Quote $quote, $productId)
    {
        /** @var $quoteItems \Magento\Quote\Model\Quote\Item[] */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem) {
            if ($productId == $quoteItem->getProductId()) {
                return $quoteItem;
            }
        }
        return null;
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $amastyRuleModel = $this->objectManager->create(\Amasty\Rules\Model\Rule::class)
            ->load($this->currentTestedRuleId, 'salesrule_id');

        if ($amastyRuleModel->getId()) {
            $amastyRuleModel->delete();
        }
    }

    /**
     * @param $salesRuleId
     */
    protected function prepareAmastyRulesData($salesRuleId)
    {
    }
}
