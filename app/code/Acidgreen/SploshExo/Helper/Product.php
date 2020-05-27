<?php

namespace Acidgreen\SploshExo\Helper;

use Acidgreen\Exo\Helper\ProductInterface;
use Acidgreen\Exo\Helper\Category as CategoryHelper;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Psr\Log\LoggerInterface;
use Magento\Framework\Registry;
use Acidgreen\SploshExo\Model\Config\Source\Box as BoxOption;

// SPL-123
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Acidgreen\SploshBox\Model\ResourceModel\Box\CollectionFactory as BoxCollectionFactory;

use Magento\Catalog\Model\Category as CategoryModel;

class Product implements ProductInterface
{
    /**
     * @var \Acidgreen\Exo\Helper\Category
     */
    protected $categoryHelper;

    /**
     * @var array
     */
    protected $categoryPaths;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    protected $_registry;

    /**
     * @var BoxCollectionFactory
     */
    protected $boxCollectionFactory;

      /**
       * @var \Psr\Log\LoggerInterface
       */
  	protected $logger;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productCollectionExcluded;

    /**
     * SPL-337 - StoreManagerInterface dependency for setting store_id 
     * on where to save the "blank" carton size
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * SPL-364 - Updated category positions holder
     */
    protected $updatedCategoryProductList;

  	public function __construct(
        CategoryHelper $categoryHelper,
        ConfigHelper $configHelper,
        ProductCollectionFactory $productCollectionFactory,
        LoggerInterface $logger,
        BoxCollectionFactory $boxCollectionFactory,
        Registry $registry,
        CategoryModel $categoryModel,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Store\Model\StoreManagerInterface $storeManager // SPL-337
  	) {
        $this->logger = $logger;
        $this->categoryHelper 		= $categoryHelper;
        $this->categoryPaths		= $this->categoryHelper->getCategoryPaths();
        $this->productCollectionFactory = $productCollectionFactory;
        $this->configHelper = $configHelper;
        $this->_registry = $registry;
        $this->boxCollectionFactory = $boxCollectionFactory;
        $this->categoryModel = $categoryModel;
        $this->sessionStorage = $storage;

        /**
         * SPL-337 - Inject store manager dependency for $product->setStoreId purposes...
         */
        $this->storeManager = $storeManager;

        /**
         * SPL-364 - initialize $updatedCategoryProductList
         */
        $this->updatedCategoryProductList = [];
  	}
  	/**
  	 * Implement inventory qty calculation
  	 * {@inheritDoc}
  	 * @see \Acidgreen\Exo\Helper\ProductInterface::getQty()
  	 */
  	public function getQty(\SimpleXMLElement $exoProductData)
  	{
  		// XPATH: SimpleStockItem/StockLevels/Physical

  		$physicalQty = 0;

  		$this->logger->debug(__('%1 :: return physicalQty = %2', __METHOD__, $physicalQty));
  		return $physicalQty;
  	}

    /**
    * Implement category data mapping
    * {@inheritDoc}
    * @see \Acidgreen\Exo\Helper\ProductInterface::getCategoryImportData()
    */
    public function getCategoryImportData($exoProductData, $site = null)
    {
        // return ProductInterface::DEFAULT_CATEGORY_ID;
        $category = array();

        $categoryField = $this->configHelper->getScopeConfigWebsite(
            self::CONFIG_CATEGORY_MAPPING_FIELD,
            $this->configHelper->getExoCurrentWebsiteId()
            );

        /**
         * SPL-323
         * For AU B2B and AU B2C websites, get data from here - $exoProduct->xpath(...)
         * For NZ B2B and NZ B2C websites, get data probably from $product->getCategoryIds()
         */
        $categoryImportData = array();

        if ($this->shouldMapCategoryFromApi($site)) {
            $exoCategoryImportData = $exoProductData->xpath($categoryField);
            $categoryImportData = $this->categoryIdsToArray($exoCategoryImportData[0]->__toString());
        } else {
            $exoCategoryImportData = $this->getExistingCategoryImportData($exoProductData);
            $categoryImportData = $exoCategoryImportData;
        }

        // THIS SHOULD ONLY BE FOR AU!
        $removeCategory = array();

        $isCountryNotNZ = $this->isCountryNotNZ($site);
        if (!empty($site) && $isCountryNotNZ) {
            if (strpos($site, 'b2b') !== false) {
                $removeCategory = $this->getB2cCategoryIds();
            } else {
                $removeCategory = $this->getB2bCategoryIds();
            }
        } else { // SPL-327 extension
            if (!$isCountryNotNZ) {
                if (strpos($site, 'b2b') !== false) {
                    $removeCategory = $this->getB2cCategoryIds();
                } else {
                    $removeCategory = $this->getB2bCategoryIds();
                }
            }
        }
        $sku = $exoProductData->xpath('Id');

        foreach ($categoryImportData as $categoryId) {
            if (!empty($removeCategory)) {
                if (in_array($categoryId, $removeCategory)) {
                continue;
                }
            }

            // $this->categoryPaths?
            if (isset($this->categoryPaths[$categoryId])) {
                /**
                 * SPL-364 - Add each product ID to $updatedCategoryProductList
                 */
                if (!isset($this->updatedCategoryProductList[$categoryId])) 
                    $this->updatedCategoryProductList[$categoryId] = [];

                $skuStr = $sku[0]->__toString();
                if (isset($this->_productCollection[$skuStr])) {
                    $productId = $this->_productCollection[$skuStr]->getId();
                }

                /**
                 * SPL-364 - Preventive fix to avoid product sync issue on live Friday, 02/23/2018
                 */
                if (!empty($productId)) {
                    $this->updatedCategoryProductList[$categoryId][$productId] = count($this->updatedCategoryProductList[$categoryId]);
                }

                $category[] = $this->categoryPaths[$categoryId];
            }
        }

        if (empty($category)) {
            $category = self::DEFAULT_CATEGORY_STRING;
        } else {
            $category = implode(',', $category);
        }

        return $category;
    }

    public function getB2cCategoryIds()
    {
        $filteredCategories = array();
        foreach ($this->categoryPaths as $categoryId => $categoryName) {
            $categoryName = strtolower($categoryName);
            if (strpos($categoryName, 'b2c') !== false) {
                $filteredCategories[] = $categoryId;
            }
        }
        return $filteredCategories;
    }

    public function getB2bCategoryIds()
    {
        $filteredCategories = array();
        foreach ($this->categoryPaths as $categoryId => $categoryName) {
            $categoryName = strtolower($categoryName);
            if (strpos($categoryName, 'b2b') !== false) {
                $filteredCategories[] = $categoryId;
            }
        }
        return $filteredCategories;
    }

  	/**
  	 * Convert the category IDS to array
  	 * @param string $categoryImportData
  	 * @return array
  	 */
  	private function categoryIdsToArray($categoryImportData)
  	{
  		$categoryIdsArray = explode(',', $categoryImportData);

  		$categoryIdsArray = array_filter($categoryIdsArray, function($e){
  			return $e != '';
  		});

  		return $categoryIdsArray;
  	}

    /**
     * Get existing category import data (for NZ B2C/NZ B2B websites)
     * @return array
     */
    private function getExistingCategoryImportData($exoProductData)
    {
        $existingCategoryImportData = [];

        $productCollection = $this->getProductCollection();

        $sku = $exoProductData->xpath('Id');
        $sku = $sku[0]->__toString();

        if (isset($productCollection[$sku])) {
            $existingCategoryImportData = $productCollection[$sku]->getCategoryIds();
        }

        return $existingCategoryImportData;
    }

  	/**
  	 *
  	 * {@inheritDoc}
  	 * @see \Acidgreen\Exo\Helper\ProductInterface::getProductType()
  	 */
  	public function getProductType($exoProductData)
  	{
  		return \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;
  	}

  	public function mapExclusiveProductColumns($exoProductData)
  	{
          $productData = [];

          // boxing features
          $ctnSizeData = $exoProductData->xpath('ExtraFields[Key="X_CTN_SIZE"]/Value');

          $productData = [
              // 'exo_ctn_size' => '',
          ];

        if (!empty($ctnSizeData) && !empty($ctnSizeData[0])) {
            $boxOptions = $this->getBoxOptions();

            if (!empty($ctnSizeData[0]->__toString()))
            	if (isset($boxOptions[$ctnSizeData[0]->__toString()]))
                	$productData['exo_ctn_size'] = $ctnSizeData[0]->__toString();
        }

          /**
           * Min. Sale Qty mapping
           */
          /*
          $minQtyData = $exoProductData->xpath('ExtraFields[Key="X_CUSTOMFIELD3"]/Value');
          if (!empty($minQtyData) && isset($minQtyData[0])) {
              if (!empty($minQtyData[0]->__toString())) {
                  $productData['min_sale_qty'] = (int)$minQtyData[0]->__toString();
              }
          }
           */
        // Due date
        $dueDateData = $exoProductData->xpath('ExtraFields[Key="X_DUEDATE"]/Value');

        if (!empty($dueDateData)) {
            $productData['exo_due_date'] = $dueDateData[0]->__toString();
        }


        // backorders - map only for b2b
        $exoCurrentWebsiteId = $this->configHelper->getExoCurrentWebsiteId();
        $backorderXpath = 'ExtraFields[Key="X_OOS"]/Value';
        $exoForceBackorder = $exoProductData->xpath($backorderXpath);
        $forceBackorder = 0;
        if (!empty($exoForceBackorder) && !empty($exoForceBackorder[0]->__toString())) {
            /**
             * SPL-349 - Cater to NZ B2B needs
             */
            $websiteIdArray = [5, 6];
            $websiteCodeArray = ['au_web_b2b', 'nz_web_b2b'];
            if (in_array($exoCurrentWebsiteId, $websiteIdArray)
            || in_array($exoCurrentWebsiteId, $websiteCodeArray)) {
                $forceBackorder = $exoForceBackorder[0]->__toString();
                $forceBackorder = ($forceBackorder == 'Y' || $forceBackorder == '1');
            }
        }
        $productData['force_backorder'] = $forceBackorder;

  		return $productData;
  	}

    /**
     * Load product collection array keyed by SKU/EXO Customer ID
     * @param boolean $reload ("load" again the product collection, defaulted to false)
     * @return array
     */
    public function getProductCollection($reload = false)
    {
        $websiteStores = [
            'base' => 'default',
            'b2c_nz_web' => 'nz_b2c_store_view',
            'au_web_b2b' => 'au_b2b_store_view',
            'nz_web_b2b' => 'nz_b2b_store_view',
        ];
        if (empty($this->_productCollection) || $reload) {
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect('*');
            $productCollection->setFlag('has_stock_status_filter', false);
            /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
            // $storeManager = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');

            $store = $this->storeManager->getStore($websiteStores[$this->configHelper->getExoCurrentWebsiteId()]);

            $products =  [];

            foreach ($productCollection as $product) {
                $products[$product->getSku()] = $product;
            }
            $this->_productCollection = $products;
        }

        return $this->_productCollection;

    }

    private function getBoxOptions()
    {
        $boxCollection = $this->boxCollectionFactory->create();

		$boxCollection->addFieldToFilter('is_active', true);

        $boxOptions = [];
        if (count($boxCollection) > 0) {
            foreach ($boxCollection as $box) {
                $boxOptions[$box->getBoxType()] = $box->getBoxType();
            }
        }

        return $boxOptions;
    }

    public function getCategoryProductPositions() {
        $array = array();
        foreach ($this->categoryModel->getCollection() as $category) {
            $array[$category->getId()] = $category->getProductsPosition();
        }

        return $array;
    }

    public function setOldProductPositions($oldPositionArray) {
        $connection = $this->categoryModel->getResource()->getConnection();

        foreach ($oldPositionArray as $categoryId => $productPositionArray) {
            //get the products position again after EXO import
            $newPositionsArray = $this->categoryModel->getResource()->getProductsPosition($this->categoryModel);
            
            /**
             * SPL-364 - reconcile what was added..
             */
            $updatedCategoryProductListKeys = [];
            $oldPositionArrayCategoryKeys = [];

            if (!empty($this->updatedCategoryProductList[$categoryId]))
                $updatedCategoryProductListKeys = array_keys($this->updatedCategoryProductList[$categoryId]);
            if (!empty($oldPositionArray[$categoryId]))
                $oldPositionArrayCategoryKeys = array_keys($oldPositionArray[$categoryId]);

            // array_diff($1, $2)
            $newProducts = array_diff($updatedCategoryProductListKeys, $oldPositionArrayCategoryKeys);

            foreach ($productPositionArray as $productId => $position) {
                $where = ['category_id = ?' => (int)$categoryId, 'product_id = ?' => (int)$productId];
                $bind = ['position' => (int)$position];
                $connection->update($this->categoryModel->getResource()->getCategoryProductTable(), $bind, $where);
            }

            if(!empty($newProducts)){
                $ctr = 0;
                
                foreach ($newProducts as $productId) {
                    $newPosition = count($productPositionArray) + $ctr;

                    $where = ['category_id = ?' => (int)$categoryId, 'product_id = ?' => (int)$productId];
                    $bind = ['position' => (int)$newPosition];
                    $connection->update($this->categoryModel->getResource()->getCategoryProductTable(), $bind, $where);

                    $ctr++;
                }
            }
        }
    }

    public function setAsExoImport() {
        $this->sessionStorage->setData('is_exo_import', true);
    }

    public function setAsCsvImport() {
        $this->sessionStorage->setData('is_exo_import', false);
    }

    public function isExoImport() {
        return $this->sessionStorage->getData('is_exo_import');
    }

    public function isCsvImport() {
        return !$this->sessionStorage->getData('is_exo_import');
    }

    /**
     * Should i map the qty field for this sync?
     * @return boolean (if website == AU B2B/AU B2C)
     */
    public function shouldMapQty($website)
    {
        // AU only
        return !preg_match('/nz/', $website);
    }

    /**
     * SPL-327
     * Should i map category based from the EXO API results?
     * @return boolean (if website == AU B2B/AU B2C)
     */
    public function shouldMapCategoryFromApi($website)
    {
        // AU only
        return !preg_match('/nz/', $website);
    }

    /**
     * is country == 'NZ'
     * @return boolean (true if website code does not contain nz)
     */
    private function isCountryNotNZ($website)
    {
        return !preg_match('/nz/', $website);
    }

    /**
     * SPL-337 - "Empty" columns that have value/s previously
     * Columns involved will be defined in an array
     */
    public function resaveIfNotSaved(&$dataArray)
    {
        $websiteStores = [
            'base' => 'default',
            'b2c_nz_web' => 'nz_b2c_store_view',
            'au_web_b2b' => 'au_b2b_store_view',
            'nz_web_b2b' => 'nz_b2b_store_view',
        ];

        $resaveIfNotSavedColumns = [
            'exo_ctn_size'
        ];
        $productCollection = $this->getProductCollection(true);

        $this->logger->info('SPL-337 :: resaveIfNotSaved...');

        $ctr = 0;
        foreach ($dataArray as $row) {
            if (isset($productCollection[$row['sku']])) {
                $product = $productCollection[$row['sku']];
                // save for that store only!!!
                $store = $this->storeManager->getStore($websiteStores[$this->configHelper->getExoCurrentWebsiteId()]);
                $this->logger->info('SPL-337 :: resaveIfNotSaved :: $product->setStoreId to :: '.$store->getId());
                $product->setStoreId($store->getId());
                foreach ($resaveIfNotSavedColumns as $column) {
                    if (!empty($product->getData($column))) {
                        $value = (empty($row[$column])) ? '' : $row[$column];
                        $this->logger->info(__('SPL-337 :: resaveIfNotSaved :: setData(%1, %2) :: SKU %3', $column, $value, $row['sku']));
                        $product->setData($column, $value);
                        $product->getResource()->saveAttribute($product, $column);
                    }
                }
                $this->logger->info('SPL-337 :: resaveIfNotSaved :: check if row was updated...');
            }
        }
    }

    /**
     * Create a product collection of products to exclude?
     * @todo logic for this method...
     */
    protected function getProductCollectionExcluded()
    {
        if (!isset($this->_productCollectionExcluded)) {
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToFilter('type_id', \Magento\GiftCard\Model\Catalog\Product\Type\Giftcard::TYPE_GIFTCARD);
            $collection->load();

            $this->_productCollectionExcluded = [];

            foreach ($collection as $product) {
                $this->_productCollectionExcluded[$product->getSku()] = $product;
            }
        }
        return $this->_productCollectionExcluded;
    }

    public function isProductGiftcard($exoProductId)
    {
        $productCollectionExcluded = $this->getProductCollectionExcluded();
        return (isset($productCollectionExcluded[$exoProductId]));
    }
}
