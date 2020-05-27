<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreManagerInterface;

class Store extends AbstractVariable
{
    protected $supportedTypes = ['Magento\Store\Model\Store'];

    /**
     * @param StoreFactory          $storeFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface  $scopeConfig
     */
    public function __construct(
        StoreFactory $storeFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct();

        $this->storeFactory = $storeFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if ($this->context->getData('store')) {
            return $this->context->getData('store');
        }

        if ($this->context->getData('store_id')) {
            $store = $this->storeFactory->create()->load($this->context->getData('store_id'));
        } else {
            $store = $this->storeManager->getDefaultStoreView();
        }

        $this->context->setData('store', $store);

        return $store;
    }

    /**
     * Store name
     *
     * @return string
     */
    public function getStoreName()
    {
        return $this->getStore()->getFrontendName();
    }

    /**
     * Store email
     *
     * @return string
     */
    public function getStoreEmail()
    {
        return $this->scopeConfig->getValue('trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    /**
     * Store email sender name
     *
     * @return string
     */
    public function getStoreEmailSenderName()
    {
        return $this->scopeConfig->getValue('trans_email/ident_general/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    /**
     * Store phone
     *
     * @return string
     */
    public function getStorePhone()
    {
        return $this->scopeConfig->getValue('general/store_information/phone',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    /**
     * Store Country
     *
     * @return string
     */
    public function getStoreCountry()
    {
        return $this->scopeConfig->getValue('general/store_information/country_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    /**
     * Store Region/State
     *
     * @return string
     */
    public function getStoreRegion()
    {
        return $this->scopeConfig->getValue('general/store_information/region_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    /**
     * Store ZIP/Postal Code
     *
     * @return string
     */
    public function getStorePostalCode()
    {
        return $this->scopeConfig->getValue('general/store_information/postcode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    /**
     * Store City
     *
     * @return string
     */
    public function getStoreCity()
    {
        return $this->scopeConfig->getValue('general/store_information/city',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }
     
    /**
     * Store Street Address
     *
     * @return string
     */
    public function getStoreStreet()
    {
        return $this->scopeConfig->getValue('general/store_information/street_line1',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    } 

    /**
     * Store Street Address Line 2
     *
     * @return string
     */
    public function getStoreStreet2()
    {
        return $this->scopeConfig->getValue('general/store_information/street_line2',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    } 

    /**
     * Store url
     *
     * @return string
     */
    public function getStoreUrl()
    {
        return $this->getStore()->getBaseUrl();
    }
}
