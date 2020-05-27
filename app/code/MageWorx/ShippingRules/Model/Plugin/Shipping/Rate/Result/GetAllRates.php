<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Plugin\Shipping\Rate\Result;

/**
 * Class GetAllRates
 */
class GetAllRates
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory
     */
    protected $errorFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \MageWorx\ShippingRules\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \MageWorx\ShippingRules\Model\Plugin\CollectValidMethods
     */
    private $collectValidMethodsPlugin;

    /**
     * @var \MageWorx\ShippingRules\Model\RulesApplier
     */
    private $rulesApplier;

    /**
     * GetAllRates constructor.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $errorFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \MageWorx\ShippingRules\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \MageWorx\ShippingRules\Model\Plugin\CollectValidMethods $collectValidMethodsPlugin
     * @param \MageWorx\ShippingRules\Model\RulesApplier $rulesApplier
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $errorFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MageWorx\ShippingRules\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \MageWorx\ShippingRules\Model\Plugin\CollectValidMethods $collectValidMethodsPlugin,
        \MageWorx\ShippingRules\Model\RulesApplier $rulesApplier
    ) {
        $this->errorFactory              = $errorFactory;
        $this->scopeConfig               = $scopeConfig;
        $this->helper                    = $helper;
        $this->request                   = $request;
        $this->collectValidMethodsPlugin = $collectValidMethodsPlugin;
        $this->rulesApplier              = $rulesApplier;
    }

    /**
     * Disable the marked shipping rates. Rates disabling in the
     *
     * @see \MageWorx\ShippingRules\Model\RulesApplier::disableShippingMethod()
     *
     * NOTE: If you can not see some of the shipping rates, start debugging from here. At first, check 'is_disabled'
     * param in the shipping rate object.
     *
     * @param \Magento\Shipping\Model\Rate\Result $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllRates($subject, $result)
    {
        if ($this->request->getRouteName() == 'multishipping') {
            /**
             * This plugin should work only on the regular checkout/cart
             */
            return $result;
        }

        if ($this->helper->getShippingPerProduct()) {
            $availableShippingMethods = $this->collectValidMethodsPlugin->getAvailableShippingMethods();
        } else {
            $availableShippingMethods = [];
        }

        $appliedCarriers = [];

        /**
         * Filter rates in result by minimal price to display only one rate from stack
         */
        $this->filterRatesByMinPrice($result);

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method[] $result */
        /**
         * @var int $key
         * @var \Magento\Quote\Model\Quote\Address\RateResult\Method $rate
         */
        foreach ($result as $key => $rate) {
            $code = \MageWorx\ShippingRules\Model\Rule::getMethodCode($rate);

            if ($this->helper->getShippingPerProduct()) {
                $rateIsAvailable = \in_array($code, $availableShippingMethods);
            } else {
                $rateIsAvailable = true;
            }

            if ($rate->getIsDisabled() || !$rateIsAvailable) {
                if ($rate->getShowError()) {
                    if (!empty($appliedCarriers[$rate->getCarrier()])) {
                        unset($result[$key]);
                        continue;
                    } else {
                        $appliedCarriers[$rate->getCarrier()] = 1;
                    }

                    if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
                        continue;
                    }

                    /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
                    $error = $this->errorFactory->create();
                    $error->setCarrier($rate->getCarrier());
                    $error->setMethod($rate->getMethod());
                    $error->setCarrierTitle($rate->getCarrierTitle());
                    $defaultErrorMessage = $this->getDefaultErrorMessage($rate->getCarrier());
                    $customErrorMessage  = $rate->getCustomErrorMessage();
                    $error->setErrorMessage($customErrorMessage ? $customErrorMessage : $defaultErrorMessage);
                    $result[$key] = $error;
                } else {
                    unset($result[$key]);
                }
            }
        }

        if ($this->helper->displayCheapestRateAtTop()) {
            uasort(
                $result,
                function ($first, $second) {
                    return ($first->getPrice() >= $second->getPrice());
                }
            );
        }

        return $result;
    }

    /**
     * Filter rates by minimal price
     *
     * @param array $result
     * @return array
     */
    private function filterRatesByMinPrice(array $result = [])
    {
        if (count($result) <= 1) {
            return $result;
        }

        $rulesWithFilteredRates = $this->rulesApplier->getShippingMethodsFilterRules();
        ksort($rulesWithFilteredRates, SORT_NUMERIC);

        /** @var \MageWorx\ShippingRules\Model\Rule[] $rules */
        $rules = [];
        foreach ($rulesWithFilteredRates as $sortedRules) {
            $rules += $sortedRules;
        }

        foreach ($rules as $rule) {
            $result = $rule->filterRatesByMinimalPrice($result);
        }

        return $result;
    }

    /**
     * @param string $carrierCode
     * @return \Magento\Framework\Phrase|string
     */
    private function getDefaultErrorMessage($carrierCode)
    {
        return $this->scopeConfig->getValue('carriers/' . $carrierCode . '/specificerrmsg') ?
            $this->scopeConfig->getValue('carriers/' . $carrierCode . '/specificerrmsg') :
            __('Sorry, but we can\'t deliver to the destination country with this shipping module.');
    }
}
