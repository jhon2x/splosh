<?php

namespace Acidgreen\SploshExo\Model\Import;

use Magento\ImportExport\Model\ImportFactory;
use Acidgreen\Exo\Model\Import\AbstractImporter;
use Acidgreen\Exo\Model\ArrayAdapterFactory;
use Acidgreen\Exo\Model\ProcessFactory;
use Acidgreen\Exo\Helper\Category as CategoryHelper;
use Acidgreen\Exo\Helper\Data as HelperClass;
use Acidgreen\Exo\Helper\ImportError as ErrorHelper;
use Acidgreen\Exo\Helper\Api\Api as ApiHelper;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Acidgreen\Exo\Helper\ProductInterface as ProductHelper;
use Psr\Log\LoggerInterface as Logger;
use GuzzleHttp\Stream\Stream as GuzzleHttpStream;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Framework\Registry as CoreRegistry;
use Magento\Store\Model\Website as WebsiteModel;
use Magento\Store\Model\StoreManagerInterface;
use Acidgreen\SploshBackorder\Model\StockzoneRegistry;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class Product extends \Acidgreen\Exo\Model\Import\Product
{
	/**
	 * @var StockzoneRegistry
	 */
	protected $stockzoneRegistry;

    /**
     * @var Registry
     */
    protected $positionRegistry;

    /**
     * @var array
     */
    protected $previousProductPositions;

    /**
     * @var array
     */
    protected $stockzoneItems;

    /**
     * @var array
     */
    protected $forceBackorderProductData;

    /**
    * Product Import Contructor
    *
    * @param ImportFactory $importModelFactory
    * @param ArrayAdapterFactory $arrayAdapterFactory
    * @param ErrorHelper $errorHelper
    * @param ProcessFactory $processFactory
    * @param HelperClass $helper
    * @param ApiHelper $apiHelper
    * @param Logger $logger
    * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
    * @param \Magento\Store\Model\Website $_websiteModel
    * @param StockzoneRegistry $stockzoneRegistry
    * @param Registry $positionRegistry
    */
  	public function __construct(
        ImportFactory $importModelFactory,
        ArrayAdapterFactory $arrayAdapterFactory,
        ErrorHelper $errorHelper,
        ProcessFactory $processFactory,
        CategoryHelper $categoryHelper,
        HelperClass $helper,
        ApiHelper $apiHelper,
        ConfigHelper $configHelper,
        ProductHelper $productHelper,
        Logger $logger,
        IndexerFactory $indexerFactory,
        StoreManagerInterface $_storeManager,
        CoreRegistry $_coreRegistry,
        WebsiteModel $_websiteModel,
  		StockzoneRegistry $stockzoneRegistry,
        Registry $positionRegistry
  	)
  	{
  		$this->stockzoneRegistry = $stockzoneRegistry;

        $this->positionRegistry	= $positionRegistry;

  		$this->previousProductPositions = array();

        // SPL-231
        $this->stockzoneItems = [];

        // SPL-295
        $this->forceBackorderProductData = [];

        parent::__construct(
            $importModelFactory,
            $arrayAdapterFactory,
            $errorHelper,
            $processFactory,
            $categoryHelper,
            $helper,
            $apiHelper,
            $configHelper,
            $productHelper,
            $logger,
            $indexerFactory,
            $_storeManager,
            $_coreRegistry,
            $_websiteModel
        );
  	}

    /**
     * Initialize Import
     */
    public function initImport($process)
    {
        $this->logger->debug(__METHOD__);
        $this->process = $process;

        $this->configHelper->unsetExoCurrentWebsite();
        $this->configHelper->setExoCurrentWebsite($this->process->getWebsites());
        $this->productFields = $this->configHelper->parseProductMappingConfig();

        parent::initImport($process);
    }

    /**
       * Get Import Data
       *
       * @return Array $data
       */
    protected function getImportData()
    {
				$this->productHelper->setAsExoImport();
        $data = [];

        // put this in a loop
        $page = 1;
        $TEST_PAGE_LIMIT = 1;

        //$pagesize = 20; //$this->configHelper->getApiPagesize();
        $pagesize = $this->configHelper->getApiPagesize();

        $data['products'] = [];

        $tempProductsArrayCount = 1;
        $productsXmlString = '<ArrayOfSimpleStockItem>';

        // Set condition to "while ($page <= $TEST_PAGE_LIMIT)..." for debugging purposes
        while ($tempProductsArrayCount > 0)
        {
            // we're setting this to 0 to prevent an endless loop
            $tempProductsArrayCount = 0;

            $productResponse = $this->apiHelper->getAllActiveProducts([
              'page' => $page,
              'pagesize' => $pagesize,
              ]);

            if($productResponse['status'] == '200') {

                $body   = \GuzzleHttp\Ring\Core::body($productResponse);

                $tempBody = $body;

                $tempBodyXml = new \SimpleXMLElement($tempBody);
                $tempProductsArrayCount = count($tempBodyXml);

                $body = (str_replace('<ArrayOfSimpleStockItem>', '', $body));
                $body = (str_replace('</ArrayOfSimpleStockItem>', '', $body));
                $productsXmlString .= $body;
            } else {
                $this->logger->debug(__('%1 :: WARNING :: Not all products may be synced due to the %2 issue from the request.', __METHOD__, $productResponse['status']));
            }

            $page++;
            sleep(1);
        }

        $productsXmlString .= '</ArrayOfSimpleStockItem>';

        $productsXml = new \SimpleXMLElement($productsXmlString);
        return $productsXml;

  	}

    /**
    * Get product data, parse it
    * @todo refactory Category mapping
    *
    * @return Array @products
    */
    protected function getProductData()
    {
        $rawData = $this->importData;
        // keyed by url_key => # of occurences for the key
        $urlKeys = [];
        $duplicateUrlKeys = [];

        $products = [];

        // product image filename suffix
        $imageSuffix = '.';

        // loop counter for debugging...
        $ctr = 1;

        $this->previousProductPositions = $this->productHelper->getCategoryProductPositions();

        $this->positionRegistry->register('products_position', $this->previousProductPositions);

        /**
         * SPL-364 - load product collection keyed by SKU for product sorting purposes...
         */
        $this->productHelper->getProductCollection(true);

        foreach ($rawData as $exoProductData) {
            $description = $exoProductData->xpath('Description[1]');

            $id = $exoProductData->xpath('Id');

            $stockType = $exoProductData->xpath('StockType');

            if (!isset($description[0]) || $description[0]->__toString() == '') {
                $ctr++;
                continue;
            }

            if (!isset($id[0]) || $id[0]->__toString() == '@') {
                $ctr++;
                continue;
            }

            /**
             * SPL-404 - exclude Gift Cards from product syncs
             */
            if ($this->productHelper->isProductGiftcard($id[0]->__toString())) {
                $ctr++;
                continue;
            }

            if (!isset($stockType[0]) || $stockType[0]->__toString() == 'LookupItem') {
                $ctr++;
                continue;
            }

            $mappedProduct = $this->mapProductColumns($exoProductData);


            $product = [
                'attribute_set_code' => 'Default',
                'product_type' => 'simple',
                'url_key' => $this->formatUrlKey($mappedProduct['sku'] . '-' . $mappedProduct['name']),
                'product_websites' => $this->process->getWebsites(),
                'store_view_code' => $this->getStoreViewCode(),
                'visibility' => 'Catalog, Search'
            ];
            // SPL-327
            $categoryImportData = $this->productHelper->getCategoryImportData($exoProductData, $this->process->getWebsites());
            $product['categories'] = $categoryImportData;

            $minQty = $exoProductData->xpath('ExtraFields[Key="X_CUSTOMFIELD3"]/Value');
            if (!empty($minQty) && isset($minQty[0])) {
                if (!empty($minQty[0]->__toString())) {
                    $product['use_config_min_sale_qty'] = '0';
                    $product['min_sale_qty'] = $minQty[0]->__toString();
                }
            }

            if ($this->productHelper->shouldMapQty($this->process->getWebsites())) {
                // Quantity (StockLevels[1]/Physical - StockLevels[1]/Committed)
                $product['qty'] = $this->getQty($exoProductData);
                $product['is_in_stock'] = $this->getIsInStock($product);

                // backorders and rest of the attributes?
                $this->stockzoneItems[$id[0]->__toString()]['qty'] = $product['qty'];
                $this->stockzoneItems[$id[0]->__toString()]['is_in_stock'] = $product['is_in_stock'];

            }

            $product = array_merge($mappedProduct, $product);

            $clientExclusiveColumns = $this->productHelper->mapExclusiveProductColumns($exoProductData);

            /**
             * SPL-295 fix
             */
            $this->forceBackorderProductData[$product['sku']] = (isset($clientExclusiveColumns['force_backorder'])) ?
                $clientExclusiveColumns['force_backorder'] : 0;

            $product = array_merge($product, $clientExclusiveColumns);

            // Product image
            $productImage = $exoProductData->xpath('WebImageFileName');
            if (!empty($productImage[0])) {

                try {
                    $imgFileName = $productImage[0]->__toString();
                    $imgFileName = trim($imgFileName);

                    $importImage = true;

                    if (preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬]/', $imgFileName)) {

                        $importImage = false;
                    }


                    if ($importImage) {
                        $imgFileNameArr = explode('.', $imgFileName);
                        if (count($imgFileNameArr) > 1) {

                            try {
                                $srcImgPath = $this->configHelper->getProductImagesSrcDir();
                                // $srcImgPath = $srcImgPath.'/'.$imgFileNameArr[0].'_0.'.$imgFileNameArr[1];
                                $srcImgPath = $srcImgPath . '/' . $imgFileNameArr[0] . $imageSuffix . $imgFileNameArr[1];

                                $newImgFileName = $imgFileNameArr[0] . '_0.' . $imgFileNameArr[1];
                                $destImgPath = BP . '/' . $this->configHelper->getProductImagesDestDir();
                                $destImgPath = $destImgPath . '/' . $newImgFileName;

                                /* copy-paste the image */
                                $srcImg = GuzzleHttpStream::factory(fopen($srcImgPath, 'r'));
                                $destImg = GuzzleHttpStream::factory(fopen($destImgPath, 'wb'));
                                $destImg->write($srcImg->getContents());
                                $destImg->close();
                                $srcImg->close();

                                // to-do: write this thing ONLY if successful
                                $product['base_image'] = '/exo/product/' . $newImgFileName;
                                $product['small_image'] = '/exo/product/' . $newImgFileName;
                                $product['thumbnail_image'] = '/exo/product/' . $newImgFileName;
                            } catch (\Exception $e) {
                                $this->logger->debug(__('%1 :: RETRYING IMAGE IMPORT by calling writeImageToPath for %2', __METHOD__, $exoProductData['id']));
                                $this->writeImageToPath($product, $imgFileNameArr);
                            }

                            if (isset($product['base_image'])) {
                                $this->logger->debug(__('%1 :: Possible base_image--%2', __METHOD__, $product['base_image']));
                            }
                        }
                    }

                } catch (\Exception $e) {
                    $this->logger->debug('SKIP IMAGE UPLOAD FOR PRODUCT: ' . $product['name'] . ' :: ' . print_r($e->getMessage(), true));
                }
                // set attributes
            } else {
                //$this->logger->debug(__('Row %1 - No image for Product: %2', $ctr, $product['name']));
            }

            if (isset($product['force_backorder']) && $product['force_backorder']) {
                $product['qty'] = '999';
                $product['is_in_stock'] = 1;
            }

            $ctr++;

            if (isset($urlKeys[$product['url_key']])) {
                $urlKeys[$product['url_key']] += 1;

                $numDuplicates = $urlKeys[$product['url_key']];
                $product['url_key'] = $product['url_key'] . '-' . $numDuplicates;
            }
            $urlKeys[$product['url_key']] = 1;
            $products[] = $product;
        }
        return $products;
    }

    public function processImport($dataArray)
    {

        if ($this->_validateData($dataArray)) {
            try {
                $this->_importData();
                // import qty to stockzones here?


                $this->stockzoneRegistry->processStockitemsToZones($this->importData, $this->stockzoneItems, $this->configHelper->getExoCurrentWebsiteId());
                $this->forceBackorderDefaultScope($this->productHelper->getProductCollection(true));
                $this->forceBackorderCurrentScope($this->productHelper->getProductCollection());
                /**
                 * SPL-337 - do some things after the data validation
                 */
                $dataArray = $this->afterProcessImport($dataArray);
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            } finally{
                $this->positionRegistry->unregister('products_position');
                $this->logger->debug('PRODUCTS SYNCING DONE! UNSET products_position.');
            }
        }
    }


    /**
     * calculate quantity for product from EXO
     * @param \SimpleXMLElement $exoProductData
     * @return number
     */
    public function getQty($exoProductData)
    {
        return $this->getCalculatedQty($exoProductData);
    }

    /**
     * Get calculated quantity (Physical - Committed)
     */
    private function getCalculatedQty($exoProductData)
    {
      	// qty = Physical - Committed
        $qty = 0;

        try {
            if (!empty($exoProductData->xpath('StockLevels[1]'))) {
                $stockLevel = $exoProductData->xpath('StockLevels[1]')[0];
                $physical = (int)$stockLevel->Physical[0];
                $committed = (int)$stockLevel->Committed[0];

                if (!empty($physical)) {
                    $qty = $physical - ((!empty($committed)) ? $committed : 0);
                }
                if ($qty < 0)
                    $qty = 0;
            }
        } catch (\Exception $e) {
            $this->logger->debug('ERROR WITH getQty: ' . $e->getMessage());
            $qty = 0;
        }
        return $qty;
    }

    /**
     * Get correct categories to assign to a product...
     * @param unknown $exoProductData
     * @return number|string
     */
    private function getCategoryImportData($exoProductData)
    {
        $categoryField = $this->configHelper->getScopeConfigWebsite(
            ProductHelper::CONFIG_CATEGORY_MAPPING_FIELD,
            $this->configHelper->getExoCurrentWebsiteId()
            );

        /**
         * SPL-323
         * For AU B2B and AU B2C websites, get data from here - $exoProduct->xpath(...)
         * For NZ B2B and NZ B2C websites, get data probably from $product->getCategoryIds()
         */
        if ($this->productHelper->shouldMapCategoryFromApi($this->process->getWebsites())) {
            $this->logger->debug('SPL-327 :: shouldMapCategoryFromApi TRUE :: get from xpath');
            $exoCategoryImportData = $exoProductData->xpath($categoryField);
        } else {
            $this->logger->debug('SPL-327 :: shouldMapCategoryFromApi FALSE :: get from existing data...');
            $exoCategoryImportData = $this->getExistingCategoryImportData($exoProductData);
        }

        // 1/2/3/4 type of data for input will do per testing of Magento 2 product import
        if (!empty($exoCategoryImportData) && !empty($exoCategoryImportData[0])) {

            if (!is_array($categoryImportData)) {
                $categoryImportData = $exoCategoryImportData[0]->__toString();
                // split the input, remove exceeding commas
                // then loop around the category ids array and search for that categoryId each iteration
                $categoryImportData = $this->categoryIdsToArray($categoryImportData);
            }

            $categoryImportDataString = '';
            foreach ($categoryImportData as $categoryId) {
                if (isset($this->categoryPaths[$categoryId])) {
                    $categoryImportDataString .= $this->categoryPaths[$categoryId] . ',';
                    //$this->logger->debug(__('%1 :: found categoryPath :: %2', __METHOD__, print_r($this->categoryPaths[$categoryId], true)));
                }
            }

            $categoryImportDataString = rtrim($categoryImportDataString, ',');
            $categoryImportData = $categoryImportDataString;
        }
        
        if (empty($categoryImportData)) {
            $categoryImportData = 'Default Category';
        }

        return $categoryImportData;
    }

    /**
     * Get existing category import data (for NZ B2C/NZ B2B websites)
     * @return array
     */
    private function getExistingCategoryImportData($exoProductData)
    {
        $existingCategoryImportData = [];

        $productCollection = $this->productHelper->getProductCollection();

        // $this->logger->debug('SPL-327 :: getExistingCategoryImportData :: '.print_r($exoProductData->xpath('Id'), true));
        $sku = $exoProductData->xpath('Id');
        $sku = $sku[0]->__toString();

        if (isset($productCollection[$sku])) {
            // $this->logger->debug('SPL-327 :: getExistingCategoryImportData :: category_ids? :: '.print_r($productCollection[$sku]->getCategoryIds(), true));
        }

        return $existingCategoryImportData;
    }

    /**
  	 * Convert the category IDS to array
  	 * @param string $categoryImportData
  	 * @return array
  	 */
  	private function categoryIdsToArray(string $categoryImportData)
  	{
        $categoryIdsArray = explode(',', $categoryImportData);

        $categoryIdsArray = array_filter($categoryIdsArray, function($e){
            return $e != '';
        });

        return $categoryIdsArray;
  	}

    /**
  	 * Return indexer IDs to be used for reindexing
  	 * @return array
  	 */
  	private function getIndexerIds()
  	{
        $indexerIds = [
            'catalog_product_category',
            'catalog_category_product',
            'catalog_product_price',
            'catalog_product_attribute',
            'cataloginventory_stock',
            'catalogsearch_fulltext',
            'catalogrule_rule',
            'catalogrule_product',
            'targetrule_product_rule',
            'targetrule_rule_product',
            'salesrule_rule'
        ];

  		return $indexerIds;
  	}

    /**
  	 * Return store code of reference website
  	 * @return string
  	 */
  	 private function getStoreViewCode()
   	 {
        return $this->process->getDefaultStoreViewCode();
   	 }

     /**
      * Write to destination path for the image to import...
      */
     private function writeImageToPath(&$product, $imgFileNameArr, $insertSuffix = false, $imageSuffix = null)
     {
         try {
             $srcImgPath = $this->configHelper->getProductImagesSrcDir();
             $srcImgPath = $srcImgPath.'/'.$imgFileNameArr[0].(($insertSuffix) ? $imageSuffix : '.').$imgFileNameArr[1];

             $newImgFileName = $imgFileNameArr[0].(($insertSuffix) ? $imageSuffix : '.').$imgFileNameArr[1];
             $destImgPath = BP.'/'.$this->configHelper->getProductImagesDestDir();
             $destImgPath = $destImgPath.'/'.$newImgFileName;

             /* copy-paste the image */
             $srcImg = GuzzleHttpStream::factory(fopen($srcImgPath, 'r'));
             $destImg = GuzzleHttpStream::factory(fopen($destImgPath, 'wb'));
             $destImg->write($srcImg->getContents());
             $destImg->close();
             $srcImg->close();

             $product['base_image'] = '/exo/product/'.$newImgFileName;
             $product['small_image'] = '/exo/product/'.$newImgFileName;
             $product['thumbnail_image'] = '/exo/product/'.$newImgFileName;

             return;
         } catch (\Exception $e) {
             throw new \Exception($e->getMessage());
         }
         return;

     }

    private function forceBackorderDefaultScope($productCollection)
    {
        $this->logger->debug('forceBackorderDefaultScope :: PROCESSING...');
        if (!empty($productCollection)) {
            foreach ($productCollection as $product) {
                /** @var \Magento\Catalog\Model\Product $product */
                $resource = $product->getResource();
                $product->setStoreId(0);
                $product->setData('force_backorder', 0);
                $resource->saveAttribute($product, 'force_backorder');

                if (!$product->getData('exo_product_id') || $product->getData('exo_product_id') == '0') {
                    $product->setData('exo_product_id', $product->getSku());
                    $resource->saveAttribute($product, 'exo_product_id');
                }

                if (isset($this->forceBackorderProductData[$product->getSku()])
                    && $this->forceBackorderProductData[$product->getSku()] == '1') {

                    $product->setStoreId(1);
                    $product->setData('status', Status::STATUS_DISABLED);
                    $resource->saveAttribute($product, 'status');

                    $product->setStoreId(4);
                    $product->setData('status', Status::STATUS_DISABLED);
                    $resource->saveAttribute($product, 'status');
                }
            }
        }
        $this->logger->debug('forceBackorderDefaultScope :: DONE PROCESSING...');
    }
     
    private function forceBackorderCurrentScope($productCollection)
    {
        /* @var \Magento\Store\Model\Store $currentScopeStore */
        $currentScopeStore = $this->_storeManager->getStore($this->getStoreViewCode());
        $this->logger->debug('forceBackorderCurrentScope :: PROCESSING...');
        if (!empty($productCollection)) {
            foreach ($productCollection as $product) {
                if (!isset($this->forceBackorderProductData[$product->getSku()])) {
                    $this->logger->debug(__('SKIP PROCESSING FOR SKU %1 -- %2', $product->getSku(), __METHOD__));
                    continue;
                }

                $forceBackorder = $this->forceBackorderProductData[$product->getSku()];

                /** @var \Magento\Catalog\Model\Product $product */
                $resource = $product->getResource();
                $product->setStoreId($currentScopeStore->getStoreId());
                $product->setData('force_backorder', $forceBackorder);
                $resource->saveAttribute($product, 'force_backorder');
                $this->logger->debug('forceBackorderCurrentScope :: CHECK IF REALLY SAVED FOR '.$product->getId());
            }
        }
        $this->logger->debug('forceBackorderCurrentScope :: DONE PROCESSING...');
    }

    /**
     * Finish Import
     * Overrides \Acidgreen\Exo\Model\Import\Product::finishImport()
     * @return $this
     */
    protected function finishImport()
    {
        //record end time
        $this->endTime = microtime(true);
        $indexers = [];

        $executionTime = ($this->endTime - $this->startTime ) / 60;

        $this->logger->debug('finish Import. Execution Time: ' . $executionTime);

        try {

            $indexerIds = $this->getIndexerIds();

            foreach ($indexerIds as $indexerId) {
                $indexer = $this->indexerFactory->create();
                $indexer->load($indexerId);
                $this->logger->debug(__('%1 :: indexerId : %2 :: reindexAll()', __METHOD__, $indexerId));
                $indexers[] = $indexer;
            }

            foreach ($indexers as $i) {
                $this->logger->debug('SPL-327 :: NO REINDEXING FOR NOW..CHECK IF CRON INDEXER WORKS!');
                // $i->reindexAll();
            }

        } catch (\Exception $e) {
            $this->logger->debug(__('%1 :: error with reindex :: %2', __METHOD__, $e->getMessage()));
        }

        return $this;
    }


    /**
     * SPL-337 - function to change some column values before data validation
     * @param array $dataArray
     * @return array
     */
    public function afterProcessImport($dataArray)
    {
        $newDataArray = $dataArray;

        $this->productHelper->resaveIfNotSaved($newDataArray);

        return $newDataArray;
    }
}
