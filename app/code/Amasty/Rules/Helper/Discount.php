<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Helper;

/**
 * "Max amount of discount" helper.
 */
class Discount extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    public static $maxDiscount = [];

    /**
     * @var array
     */
    private $processedData = [];

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($context);

        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
     * @param \Magento\Store\Model\Store $store
     *
     * @param int $itemId
     *
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function setDiscount(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData,
        \Magento\Store\Model\Store $store,
        $itemId
    ) {
        $cachedKey = $itemId . '_' . $discountData->getBaseAmount();

        if ($rule->getAmrulesRule()->getMaxDiscount() == 0) {
            return $discountData;
        }

        if (!isset(self::$maxDiscount[$rule->getId()]) || isset($this->processedData[$rule->getId()][$cachedKey])) {
            self::$maxDiscount[$rule->getId()] = $rule->getAmrulesRule()->getMaxDiscount();
            $this->processedData[$rule->getId()] = null;
        }

        if (self::$maxDiscount[$rule->getId()] - $discountData->getBaseAmount() < 0) {
            $convertedPrice = $this->priceCurrency->convert(self::$maxDiscount[$rule->getId()], $store);
            $discountData->setBaseAmount(self::$maxDiscount[$rule->getId()]);
            $discountData->setAmount($this->priceCurrency->round($convertedPrice));
            $discountData->setBaseOriginalAmount(self::$maxDiscount[$rule->getId()]);
            $discountData->setOriginalAmount($this->priceCurrency->round($convertedPrice));
            self::$maxDiscount[$rule->getId()] = 0;
        } else {
            self::$maxDiscount[$rule->getId()] =
                self::$maxDiscount[$rule->getId()] - $discountData->getBaseAmount();
        }

        $this->processedData[$rule->getId()][$cachedKey] = true;

        return $discountData;
    }
}
