<?php

namespace Acidgreen\Theme\Block\Html;

use Magento\Framework\Exception\NoSuchEntityException;

class Salesrep extends \Magento\Framework\View\Element\Template
{
    /**
    * Session Factory
    */
    protected $_sessionFactory;

    /**
    * Http Context
    */
    protected $_httpcontext;

    /**
    * Customer Model
    **/
    protected $_customer;

    /**
    * Staff Model
    */

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Framework\App\Http\Context $httpcontext,
        \Magento\Customer\Model\Customer $customer,
        \Acidgreen\SploshExo\Model\ResourceModel\Staff\CollectionFactory $staff,
        array $data = [])
    {
        $this->_sessionFactory = $sessionFactory;
        $this->_customer = $customer;
        $this->_httpcontext = $httpcontext;
        $this->_staff = $staff;
        $this->customerData = null;
        $this->salesrepData = null;
        parent::__construct($context, $data);
    }

    /**
     * Returns the Magento Customer Model for this block
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        if ($this->customerData) {
            return $this->customerData;
        }

        try {
            $this->customerData = $this->_customer
                ->load($this->_sessionFactory->create()->getCustomer()->getId());
            return $this->customerData;
        } catch (NoSuchEntityException $e) {
            $this->customerData = null;
            return null;
        }
    }

    public function getSalesrep()
    {
        if ($this->salesrepData) {
            return $this->salesrepData;
        }

        try {
            /**
             * SPL-335 - get correct salesperson
             * Add filtering by website_id
             */
            $customer = $this->getCustomer();
            $this->salesrepData = $this->_staff->create()
                ->addFieldToFilter('exo_staff_id', $customer->getSalesperson())
                ->addFieldToFilter('website_id', $customer->getWebsiteId())
                ->getFirstItem();
            return $this->salesrepData;
        } catch (NoSuchEntityException $e) {
            $this->salesrepData = null;
            return null;
        }
    }

    /**
     * Checks if customer is logged in
     *
     * @return boolean
     */
    public function isCustomerLoggedIn()
    {
       return (bool)$this->_httpcontext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    public function getName() {
        if (!$this->getSalesrep()) {
            return '';
        }
        return $this->getSalesrep()->getName();
    }

    public function getNickname() {
        if (!$this->getSalesrep()) {
            return '';
        }
        return $this->getSalesrep()->getNickname();
    }

    public function getPhone() {
        if (!$this->getSalesrep()) {
            return '';
        }
        return $this->getSalesrep()->getPhoneNo();
    }

    public function getEmail() {
        if (!$this->getSalesrep()) {
            return '';
        }
        return $this->getSalesrep()->getEmail();
    }

    public function getJobTitle() {
        if (!$this->getSalesrep()) {
            return '';
        }
        return $this->getSalesrep()->getJobtitle();
    }

    public function displaySalesrep() {
        return (bool)$this->getCustomer()->getDisplaySalesrep();
    }
}
