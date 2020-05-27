<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acidgreen\Customer\Block\Account\Dashboard;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Dashboard Customer Info
 */
class Balance extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Directory\Model\CurrencyFact‌​ory
     */
    protected $currency;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Directory\Model\CurrencyFactory $currency,
        array $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->customer = $customer;
        $this->currency = $currency;

        parent::__construct($context, $data);
    }

    /**
     * Returns the Magento Customer Model for this block
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        try {
            return $this->currentCustomer->getCustomer();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Returns Customer Balance - Used for Exo B2B Customers
     *
     * @return float|int
     */
    public function getBalance() {
        if (!$this->getCustomer()) {
            return 0;
        }

        $customer = $this->customer->load($this->getCustomer()->getId());
        $balance = $customer->getExoBalance();

        if (!$balance) {
            return 0;
        }
        return $balance;
    }

    public function getBalanceWithCurrency() {
        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->currency->create()->load($currencyCode)->getCurrencySymbol();
        return $currency.$this->getBalance();
    }
}
