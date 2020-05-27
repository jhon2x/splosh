<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Helper;

/**
 * Product helper.
 */
class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\SalesRule\Model\Validator
     */
    protected $_validator;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $_rule;

    /**
     * @var \Magento\Directory\Model\PriceCurrency
     */
    protected $_priceCurrency;

    /**
     * @var \Amasty\Rules\Model\RuleResolver
     */
    private $ruleResolver;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\SalesRule\Model\Validator $_validator,
        \Magento\Directory\Model\PriceCurrency $priceCurrency,
        \Amasty\Rules\Model\RuleResolver $ruleResolver
    ) {
        parent::__construct($context);

        $this->_validator = $_validator;
        $this->_priceCurrency = $priceCurrency;
        $this->ruleResolver = $ruleResolver;
    }

    public function setRule($rule)
    {
        $this->_rule = $rule;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     *
     * @return float
     */
    public function getItemPrice(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $price = $this->_validator->getItemPrice($item);
        switch ($this->getPriceSelector()) {
            case 1:
                $price -= $item->getDiscountAmount() / $item->getQty();
                break;
            case 2:
                $price = $this->_priceCurrency->convert($item->getProduct()->getPrice());
                break;
        }

        return $price;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     *
     * @return float
     */
    public function getItemBasePrice(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $price = $this->_validator->getItemBasePrice($item);
        switch ($this->getPriceSelector()) {
            case 1:
                $price -= $item->getBaseDiscountAmount() / $item->getQty();
                break;
            case 2:
                $price = $item->getProduct()->getPrice();
                break;
        }

        return $price;
    }

    /**
     * Return item original price
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemOriginalPrice(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $price = $this->_validator->getItemOriginalPrice($item);
        switch ($this->getPriceSelector()) {
            case 1:
                $price -= $item->getDiscountAmount() / $item->getQty();
                break;
            case 2:
                $price = $item->getProduct()->getPrice();
                break;
        }

        return $price;
    }

    /**
     * Return item original price
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemBaseOriginalPrice(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $price = $this->_validator->getItemBaseOriginalPrice($item);
        switch ($this->getPriceSelector()) {
            case 1:
                $price -= $item->getBaseDiscountAmount() / $item->getQty();
                break;
            case 2:
                $price = $this->_priceCurrency->convert($item->getProduct()->getPrice());
                break;
        }

        return $price;
    }

    protected function getPriceSelector()
    {
        $amrulesRule = $this->ruleResolver->getSpecialPromotions($this->_rule);

        return $amrulesRule->getPriceselector();
    }
}
