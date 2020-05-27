<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Plugin\Shipping\Rate\Result;

use Magento\Checkout\Model\Session;
use Magento\Customer\Api\Data\GroupInterface;
use MageWorx\ShippingRules\Model\RulesApplier;
use MageWorx\ShippingRules\Model\Validator;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * Class Append
 */
class Append
{
    /** @var Validator */
    protected $validator;

    /** @var Session|\Magento\Backend\Model\Session\Quote */
    protected $session;

    /** @var RulesApplier */
    protected $rulesApplier;

    /** @var \Magento\Customer\Model\Session */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Number of iteration. Used to protect from recursion when quote is not exists.
     *
     * @var int
     */
    protected $protectionIterator = 1;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @param Validator $validator
     * @param RulesApplier $rulesApplier
     * @param Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $backendQuoteSession
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param HttpRequest $request
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        Validator $validator,
        RulesApplier $rulesApplier,
        Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        \Magento\Framework\App\State $state,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        HttpRequest $request
    ) {
        $this->validator    = $validator;
        $this->rulesApplier = $rulesApplier;
        if ($state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $this->session = $backendQuoteSession;
        } else {
            $this->session = $checkoutSession;
        }
        $this->customerSession = $customerSession;
        $this->storeManager    = $storeManager;
        $this->request         = $request;
    }

    /**
     * Validate each shipping method before append.
     * Apply the rules action if validation was successful.
     * Can mark some rules as disabled. The disabled rules will be removed in the class
     *
     * @see \MageWorx\ShippingRules\Observer\Sales\Quote\Address\CollectTotalsAfter
     * by checking the value of this mark in the rate object.
     *
     * NOTE: If you have some problems with the rules and the shipping methods, start debugging from here.
     *
     * @param \Magento\Shipping\Model\Rate\Result $subject
     * @param \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult|\Magento\Shipping\Model\Rate\Result $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeAppend($subject, $result)
    {
        if (!$result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            return [$result];
        }

        // Check current iteration, it should be 1 in normal
        if ($this->protectionIterator > 1) {
            // If the result is not a normal just return it as is.
            // NOTE: When the iterator is greater than 1 - recursion obtained during receipting the current quote
            // and recollecting it's totals
            return [$result];
        }

        // Do not calculate rates when it's on multishipping step
        if ($this->request->getRouteName() == 'multishipping' &&
            $this->request->getControllerName() == 'checkout' &&
            $this->request->getActionName() == 'addressesPost') {

            return [$result];
        }

        // Increase iterator to check it later when this method called anew.
        // In normal case we decrease it in the end of method (+1-1 == no recursion, method completes successfully).
        $this->protectionIterator++;

        // This keys needed later to obtain desired shipping rules
        $storeId       = $this->session->getStoreId() ?
            $this->session->getStoreId() :
            $this->storeManager->getStore()->getId();

        if ($this->request->getParam('collect_shipping_rates')) {
            $customerGroup = $this->session->getQuote() ?
                $this->session->getQuote()->getCustomerGroupId() :
                GroupInterface::NOT_LOGGED_IN_ID;
        } else {
            $customerGroup = $this->customerSession->getCustomerGroupId();
        }

        // Loading suitable shipping rules
        $this->validator->init($storeId, $customerGroup);
        // Validating the result by a conditions of the each rule
        if ($this->validator->validate($result)) {
            // Obtaining valid rules from a storage
            $rules = $this->validator->getAvailableRulesForRate($result);
            // Applying the valid rules one-by-one using it's sort order from high to low
            $result = $this->rulesApplier->applyRules($result, $rules);
        }

        // Decrease iterator: method completes successfully.
        $this->protectionIterator--;

        return [$result];
    }
}
