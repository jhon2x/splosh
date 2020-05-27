<?php

namespace Acidgreen\SploshBackorder\Plugin;

use Magento\Store\Model\StoreManagerInterface;

class StockPlugin
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * StockPlugin constructor.
     * @param StoreManagerInterface $storeManager
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;

        $this->stockHelper = $stockHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\CatalogInventory\Helper\Stock $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeAddIsInStockFilterToCollection(
        \Magento\CatalogInventory\Helper\Stock $subject,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ) {
        if ($this->_isEnabledShowOutOfStock()) {
            return [$collection];
        }

        $b2bStoreCodes = ["au_b2b_store_view","nz_b2b_store_view"];
        if(in_array($this->storeManager->getStore()->getCode(), $b2bStoreCodes)){
            $stockFlag = 'has_stock_status_filter';
            if (!$collection->hasFlag($stockFlag)) {
                $collection->setFlag($stockFlag, true);
            }
        }

        return [$collection];
    }


    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _isEnabledShowOutOfStock()
    {
        return $this->scopeConfig->getValue(
            'cataloginventory/options/show_out_of_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getCode()
        );
    }
}
