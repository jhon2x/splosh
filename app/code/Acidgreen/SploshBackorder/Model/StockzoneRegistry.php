<?php

namespace Acidgreen\SploshBackorder\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;

class StockzoneRegistry
{
    /**
     * @var StockzoneFactory
     */
    protected $stockzoneFactory;
    
    /**
     * @var Stockzone\ItemFactory
     */
    protected $stockzoneItemFactory;
    
	/**
	 * @var StoreManagerInterface
	 */
	protected $storeManager;

    /**
     * @var Stockzone\Item[]
     */
    protected $stockzoneItems;

    /**
     * B2B website codes to process for backorders
     * @var string
     */
    private $b2bWebsiteCodes;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * For getting product IDs later..
     * @var ProductFactory
     */
    protected $productFactory;
    
    /**
     * @var array
     */
    private $productCollection;

    public function __construct(
    	StoreManagerInterface $storeManager,
    	StockzoneFactory $stockzoneFactory,
    	Stockzone\ItemFactory $stockzoneItemFactory,
    	ScopeConfigInterface $scopeConfig,
        ProductFactory $productFactory
    ) {
    	$this->storeManager = $storeManager;
    	$this->stockzoneFactory = $stockzoneFactory;
    	$this->stockzoneItemFactory = $stockzoneItemFactory;
    	$this->productFactory = $productFactory;
    	
    	$this->scopeConfig = $scopeConfig;
    }

    /**
     * Add product data to stockzone item table (for backorder processing/behavior)
     * @param array $productData
     * @param array $stockzoneItemsData
     * @param string $websiteId
     * @return boolean
     */
    public function processStockitemsToZones($productData, $stockzoneItemsData, $websiteId)
    {
        $updatedProductCollection = $this->getUpdatedProductCollection();

    	$website = $this->storeManager->getWebsite($websiteId);
    	// $website->getId();
    	
    	/** @var Stockzone $stockzone */
    	$stockzone = $this->stockzoneFactory->create()->getStockzoneByWebsite($website);
    	
    	$stockzoneItems = $this->initStockzoneItems($stockzone);
    	
    	if (empty($stockzone)) {
    		return false;
    	}
    	
    	
    	/** @var \SimpleXMLElement $exoProductData */
    	foreach ($productData as $exoProductData) {
    		$stockitemData = [];
            
    		$stockitemData['splosh_stockzone_id'] = $stockzone->getId();
            $sku = $exoProductData->xpath('Id');
            if (!empty($sku)) {
                $sku = $sku[0]->__toString();
                $stockitemData['sku'] = $sku;
            } else { // skip processing if no sku!
                continue;
            }

            $stockitemData['qty'] = $this->getQty($exoProductData);

            if (!empty($sku) && isset($stockzoneItemsData[$sku]))
                $stockitemData['qty'] = $stockzoneItemsData[$sku]['qty'];

            $stockitemData['backorders'] = $this->getBackordersSetting($exoProductData, $websiteId);
            $stockitemData['use_config_backorders'] = 0;
            $stockitemData['use_config_min_qty'] = 0;

            // get is_in_stock
            $stockitemData['is_in_stock'] = 0;
            $stockitemData['manage_stock'] = 1;
            $stockitemData['use_config_manage_stock'] = 0;

            $stockzoneItem = $this->getStockzoneItem($sku);
            
            // @todo: Delete stockzone item if product is deleted already...
            try {
                // DO NOT USE $stockzoneItem->setData($stockitemData) ONLY, it empties up the ID for some reason?!
                foreach ($stockitemData as $key => $value)
                    $stockzoneItem->setData($key, $value);

                if (($stockzone->getId()))
                    $stockzoneItem->setData('id', $stockzoneItem->getId());

                // set product_id
                if (isset($updatedProductCollection[$sku]) && empty($stockzoneItem->getProductId())) {
                    $stockzoneItem->setData('product_id', $updatedProductCollection[$sku]->getId());
                }

            	$stockzoneItem->save();
            	
            	// Set EXO due date if backorder product
            	if ($stockzoneItem->getBackorders()) {
            		$this->setExoFieldsAtDefaultScope(
            			$updatedProductCollection[$sku],
    					$exoProductData
            		);
            		
            	}
            	
            } catch (\Exception $e) {
            	return false;
            }
    	}
    	
    	return true;
    }
    
    /**
     * Set EXO-related fields defaut values (e.g. for exo_due_date)
     * @param Product $product
     * @param unknown $data
     */
    protected function setExoFieldsAtDefaultScope($product, $data)
    {
    	$product->setStoreId(0);
    	// exo_due_date
    	$exoDueDate = $data->xpath('ExtraFields[Key="X_DUEDATE"]/Value');
        if (!empty($exoDueDate) && !empty($exoDueDate[0]->__toString())) {
            $product->setExoDueDate($exoDueDate[0]->__toString());
        }

        try {
            $product->save();
        } catch (\Exception $e) {

        }
    	
    }
    
    /**
     * Get appropriate Stockzone\Item model
     * @param mixed|string $sku
     * @return Stockzone\Item
     */
    protected function getStockzoneItem($sku)
    {
    	// $stockzoneItem = $this->stockzoneItemFactory->create();
    	
    	if (is_string($sku) && isset($this->stockzoneItems[$sku])) {
    		return $this->stockzoneItems[$sku];
        }
    	
    	return $this->stockzoneItemFactory->create();
    }
    
    /**
     * Get backorder setting
     * @todo "configurable" way to disable backorders for B2C like "Don't enable backorders for the following..."
     * @param \SimpleXMLElement $exoProductData
     * @return int
     */
    private function getBackordersSetting($exoProductData, $websiteId)
    {
        // if not B2B website, backorders will always be 0 for that particular stockzone item
        if (!preg_match("/$websiteId/", $this->getB2BWebsiteCodes()))
            return 0;

    	$backorderXpath = 'ExtraFields[Key="X_OOS"]/Value';
        $backorders = $exoProductData->xpath($backorderXpath);
        // empty - returns false if non-empty, non-zero value
        if (empty($backorders) || empty($backorders[0]->__toString())) {
            $backorders = 0;
        } else {
            // should be an int!
            $backorders = $backorders[0]->__toString();
            $backorders = ($backorders == 'Y' || $backorders == '1');
        }
        
        return $backorders;
    }
    
    private function getQty($exoProductData)
    {
    	$qty = 0;
    	
    	return $qty;
    }
    
    /**
     * populate stockzone items, key by SKU
     * @param Stockzone $stockzone
     * @return void
     */
    public function initStockzoneItems(Stockzone $stockzone)
    {
    	// get items
    	// key array by SKU
        $this->stockzoneItems = [];
        $stockzoneItems = [];
        
        $collection = $this->stockzoneItemFactory->create()->getCollection();
        
        $collection->addFieldToFilter('splosh_stockzone_id', $stockzone->getId());
        
        if (count($collection) > 0) {
        	/** @var Stockzone\Item $stockzoneItem */
        	foreach ($collection as $stockzoneItem) {
        		$this->stockzoneItems[$stockzoneItem->getSku()] = $stockzoneItem;
        	}
        	
        }
    }

    /**
     * Get stockzones keyed by stockzone ID
     * @return Stockzone[]
     */
    public function getStockzones()
    {
        $stockzones = [];
        $collection = $this->stockzoneFactory->create()->getCollection();
        
        foreach ($collection as $stockzone) {
        	$stockzones[$stockzone->getId()] = $stockzone;
        }
        /*
         */
        return $stockzones;
    }

    /**
     * Get B2B website codes to use for processing backorder products
     * Since B2C shouldn't have backorder products..
     * @return string
     */
    private function getB2BWebsiteCodes()
    {
        if (!$this->b2bWebsiteCodes) {
            // get it from config...
            $this->b2bWebsiteCodes = $this->scopeConfig->getValue(
            	\Acidgreen\SploshExo\Helper\Api\Config::CONFIG_B2B_WEBSITE_CODES
            );
        }
        return $this->b2bWebsiteCodes;
    }

    /**
     * Load updated product collection 
     * for fetching product IDs and saving them to their stockzone item entries.
     * @return array
     */
    private function getUpdatedProductCollection()
    {
    	if (empty($this->productCollection)) {
			$productCollection = $this->productFactory->create()->getCollection();
    		
    		$updatedProductCollection = [];
    		if (count($productCollection) > 0) {
    			foreach ($productCollection as $product) {
    				$updatedProductCollection[$product->getSku()] = $product;
    			}
    		}
    		$this->productCollection = $updatedProductCollection;
    	}

        return $this->productCollection;
    }
}
