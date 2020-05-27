<?php

namespace Acidgreen\SploshBackorder\Model\Plugin\CatalogInventory\Stock;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductFactory;

class Item
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory
    ) {
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
    }

    public function afterGetIsInStock(
        \Magento\CatalogInventory\Api\Data\StockItemInterface $item,
        $isInStock
    ) {
        
        $website = $this->storeManager->getWebsite();
        $store = $this->storeManager->getStore();
        $productId = $item->getProductId();

        if (empty($productId))
            return $isInStock;

        // bypass the below logic, stick with what we got if B2C
        if (!preg_match("/".$website->getCode()."/", "au_web_b2b,nz_web_b2b")) {
            return $isInStock;
        }
    	
        $product = $this->productFactory->create();
        $productResource = $product->getResource();
        $forceBackorder = $productResource->getAttributeRawValue($productId, 'force_backorder', $store->getId());

        /**
         * SPL-370 - use the !empty($forceBackorder) instead
         */
        $isForceBackorder = !empty($forceBackorder);
        if ($isForceBackorder) {
            return $isForceBackorder;
        }

        return $isInStock;
    }

    public function afterGetBackorders(
        \Magento\CatalogInventory\Api\Data\StockItemInterface $item,
        $backorders
    ) {
        
        $website = $this->storeManager->getWebsite();
        $store = $this->storeManager->getStore();

        $productId = $item->getProductId();

        // if B2C - backorders are always NO!
        if (!preg_match("/".$website->getCode()."/", "au_web_b2b,nz_web_b2b")) {
            return \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO;
        }
    	
        $product = $this->productFactory->create();
        $productResource = $product->getResource();
        $forceBackorder = $productResource->getAttributeRawValue($productId, 'force_backorder', $store->getId());

        if (!empty($forceBackorder)) 
            return \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NONOTIFY;

        return $backorders;
    }
}
