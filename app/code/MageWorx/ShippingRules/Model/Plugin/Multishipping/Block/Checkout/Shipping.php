<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Plugin\Multishipping\Block\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\ShippingRules\Model\Plugin\CollectValidMethodsFactory;
use MageWorx\ShippingRules\Model\Plugin\CollectValidMethods;
use MageWorx\ShippingRules\Model\RulesApplier;
use MageWorx\ShippingRules\Model\Validator;
use MageWorx\ShippingRules\Model\ValidatorFactory;

/**
 * Class Shipping
 */
class Shipping
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var Session|\Magento\Backend\Model\Session\Quote
     */
    private $session;

    /**
     * @var RulesApplier
     */
    private $rulesApplier;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ValidatorFactory
     */
    private $validatorFactory;

    /**
     * @var CollectValidMethods
     */
    private $collectValidMethodsPlugin;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ValidatorFactory $validatorFactory
     * @param RulesApplier $rulesApplier
     * @param Session $checkoutSession
     * @param CustomerSession $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CollectValidMethodsFactory $collectValidMethodsPluginFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ValidatorFactory $validatorFactory,
        RulesApplier $rulesApplier,
        Session $checkoutSession,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        CollectValidMethodsFactory $collectValidMethodsPluginFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->validatorFactory = $validatorFactory;
        $this->rulesApplier     = $rulesApplier;
        $this->session          = $checkoutSession;
        $this->customerSession  = $customerSession;
        $this->storeManager     = $storeManager;
        $this->scopeConfig      = $scopeConfig;

        $this->collectValidMethodsPlugin = $collectValidMethodsPluginFactory->create();
    }

    /**
     * @param \Magento\Multishipping\Block\Checkout\Shipping $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetShippingRates($subject, callable $proceed, $address)
    {
        /** @var array $result */
        $result = $proceed($address);

        // This keys needed later to obtain desired shipping rules
        $storeId       = $this->session->getStoreId() ?: $this->storeManager->getStore()->getId();
        $customerGroup = $this->customerSession->getCustomerGroupId();

        /** @var \MageWorx\ShippingRules\Model\Validator validator */
        $this->validator = $this->validatorFactory->create();
        // Loading suitable shipping rules
        $this->validator->init($storeId, $customerGroup);
        $this->validator->setActualShippingAddress($address);

        $addressItems             = $address->getAllItems();
        $availableShippingMethods = $this->collectValidMethodsPlugin
            ->collectAvailableShippingMethodsForItems($addressItems);

        $groupedRates = [];
        /**
         * @var int $index
         * @var \Magento\Quote\Model\Quote\Address\Rate[] $methods
         */
        foreach ($result as $index => $methods) {
            /**
             * @var int $methodIndex
             * @var \Magento\Quote\Model\Quote\Address\Rate $someRate
             */
            foreach ($methods as $methodIndex => $someRate) {
                $rateCode = $someRate->getData('code');
                if ($rateCode && !in_array($rateCode, $availableShippingMethods)) {
                    $someRate->setIsDisabled(true);
                    $someRateProcessed = $someRate;
                } else {
                    // Validating the result by a conditions of the each rule
                    if ($this->validator->validate($someRate)) {
                        // Obtaining valid rules from a storage
                        $rules = $this->validator->getAvailableRulesForRate($someRate);
                        // Applying the valid rules one-by-one using it's sort order from high to low
                        $someRateProcessed = $this->rulesApplier->applyRules($someRate, $rules, $address);
                    } else {
                        $someRateProcessed = $someRate;
                    }
                }

                if ($someRate->getIsDisabled()) {
                    if ($someRate->getShowError()) {
                        $defaultErrorMessage = $this->getDefaultErrorMessage($someRate->getCarrier());
                        $customErrorMessage  = $someRate->getCustomErrorMessage();
                        $someRate->setErrorMessage($customErrorMessage ? $customErrorMessage : $defaultErrorMessage);
                    } else {
                        // Remove rate from stack
                        continue;
                    }
                }

                $groupedRates[$index][$methodIndex] = $someRateProcessed;
            }
        }

        return $groupedRates;
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