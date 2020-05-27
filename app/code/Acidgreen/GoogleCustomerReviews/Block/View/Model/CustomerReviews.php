<?php

namespace Acidgreen\GoogleCustomerReviews\Block\View\Model;

class CustomerReviews implements \Magento\Framework\Data\CollectionDataSourceInterface
{

    const CONFIG_PATH_ENABLED           = 'google/customer_reviews/enabled';
    const CONFIG_PATH_API_URL           = 'google/customer_reviews/api_url';
    const CONFIG_PATH_MERCHANT_ID       = 'google/customer_reviews/merchant_id';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function isCustomerReviewsEnabled()
    {
        return $this->getConfigData(self::CONFIG_PATH_ENABLED);
    }

    public function getApiUrl()
    {
        return $this->getConfigData(self::CONFIG_PATH_API_URL);
    }

    public function getMerchantId()
    {
        return $this->getConfigData(self::CONFIG_PATH_MERCHANT_ID);
    }

    public function getConfigData($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getWebsite()->getId()
        );

    }
}
