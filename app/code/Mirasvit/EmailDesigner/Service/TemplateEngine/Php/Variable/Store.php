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


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreManagerInterface;

class Store
{
    /**
     * @param StoreFactory          $storeFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface  $scopeConfig
     * @param Context               $context
     */
    public function __construct(
        StoreFactory $storeFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Context $context
    ) {
        $this->storeFactory = $storeFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->context = $context;
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
        } elseif ($this->context->getData('store_id')) {
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
        return $this->scopeConfig->getValue('trans_email/ident_general/email');
    }

    /**
     * Store phone
     *
     * @return string
     */
    public function getStorePhone()
    {
        return $this->scopeConfig->getValue('general/store_information/phone');
    }

    /**
     * Store address
     *
     * @return string
     */
    public function getStoreAddress()
    {
        return $this->scopeConfig->getValue('general/store_information/address');
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
