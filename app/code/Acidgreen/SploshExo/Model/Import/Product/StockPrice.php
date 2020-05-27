<?php

namespace Acidgreen\SploshExo\Model\Import\Product;

use Magento\ImportExport\Model\ImportFactory;
use Magento\Framework\Exception\LocalizedException;
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
use Magento\Framework\Registry;
use Magento\Store\Model\Website as WebsiteModel;
use Acidgreen\Exo\Model\Import\Product\StockPrice as ExoProductStockPrice;
use Acidgreen\SploshBackorder\Model\StockzoneRegistry;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class StockPrice extends ExoProductStockPrice
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
     * StockPrice constructor.
     * @param ImportFactory $importModelFactory
     * @param ArrayAdapterFactory $arrayAdapterFactory
     * @param ErrorHelper $errorHelper
     * @param ProcessFactory $processFactory
     * @param CategoryHelper $categoryHelper
     * @param HelperClass $helper
     * @param ApiHelper $apiHelper
     * @param ConfigHelper $configHelper
     * @param ProductHelper $productHelper
     * @param Logger $logger
     * @param IndexerFactory $indexerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param Registry $_coreRegistry
     * @param WebsiteModel $_websiteModel
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
		\Magento\Store\Model\StoreManagerInterface $_storeManager,
		CoreRegistry $_coreRegistry,
        WebsiteModel $_websiteModel,
        StockzoneRegistry $stockzoneRegistry,
        Registry $positionRegistry
    ) {
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
            $_websiteModel);
    }

    /**
     * Extend Acidgreen\Exo\Model\Import\Product\StockPrice::initImport($process)
     * @return void
     */
    public function initImport($process)
    {
        $this->process = $process;

        $this->configHelper->unsetExoCurrentWebsite();
        $this->configHelper->setExoCurrentWebsite($this->process->getWebsites());
        $this->productFields = $this->configHelper->parseProductMappingConfig();
        parent::initImport($process);
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

		$products = [];


        // loop counter for debugging...
        $ctr = 1;

        $this->previousProductPositions = $this->productHelper->getCategoryProductPositions();
		// $this->logger->debug(print_r($this->previousProductPositions,true));
        $this->positionRegistry->register('products_position', $this->previousProductPositions);

        foreach ($rawData as $exoProductData) {
            $description = $exoProductData->xpath('Description[1]');

            $id = $exoProductData->xpath('Id');

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

            $exoStockitemId = $id[0]->__toString();

            // TO-DO: if not existing in Magento, "continue"
            if (!isset($this->productCollection[$exoStockitemId])) {
                $this->logger->debug(__METHOD__.' :: ATTENTION :: PRODUCT '.$exoStockitemId. ' DOES NOT EXIST YET.');
                $ctr++;
                continue;
            }

            /**
             * SPL-338 - Pack + backorder not ignored on stock/price/backorder sync fix
             */
            $stockType = $exoProductData->xpath('StockType');

            if (!isset($stockType[0]) || $stockType[0]->__toString() == 'LookupItem') {
                $this->logger->debug(__('SPL-338 :: %1 seems to be a "Pack", skip this', $id));
                $ctr++;
                continue;
            }

            $mappedProduct = $this->mapProductColumns($exoProductData);

			$product = [
				'product_websites'       => $this->process->getWebsites(),
				'store_view_code' => $this->getStoreViewCode(),
                // SPL-285?
				'url_key' => $this->formatUrlKey($mappedProduct['sku'] . '-' . $mappedProduct['name']),
			];

            /**
             * SPL-327 - Revised category mapping
             */
			$categoryImportData = $this->productHelper->getCategoryImportData($exoProductData, $this->process->getWebsites());
			$product['categories'] = $categoryImportData;
            // potential issue: force_backorder not mapped here...

            if ($this->productHelper->shouldMapQty($this->process->getWebsites())) {
                $this->logger->debug('SPL-326 :: shouldMapQty true :: NOT AN NZ website :: map qty here... -- Product Stockprice sync');
                // Quantity (StockLevels[1]/Physical - StockLevels[1]/Committed)
                $product['qty'] = $this->getQty($exoProductData);

                // Splosh backorders processing
                $this->stockzoneItems[$exoStockitemId]['qty'] = $product['qty'];
            }

			$product = array_merge($mappedProduct, $product);

            if (isset($product['force_backorder']) && $product['force_backorder']) {
                $product['qty'] = '999';
                $product['is_in_stock'] = 1;
            }

            $ctr++;

            unset($product['description']);
            $products[] = $product;
        }

		return $products;
	}

    public function mapProductColumns($exoProductData)
    {
        $product = parent::mapProductColumns($exoProductData);

        // due date
        $dueDateData = $exoProductData->xpath('ExtraFields[Key="X_DUEDATE"]/Value');

        if (!empty($dueDateData)) {
            $product['exo_due_date'] = $dueDateData[0]->__toString();
        }

        // force_backorder
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
        $product['force_backorder'] = $forceBackorder;
        $this->forceBackorderProductData[$product['sku']] = $forceBackorder;

        return $product;
    }

    public function processImport($dataArray)
    {
        if ($this->_validateData($dataArray)) {
            try {
                $this->_importData();

                // SPL-231
                $this->stockzoneRegistry->processStockitemsToZones(
                    $this->importData,
                    $this->stockzoneItems,
                    $this->configHelper->getExoCurrentWebsiteId()
                );

                // SPL-295 / SPL-232
                $this->setExoAttributesUnderDefaultScope($this->productHelper->getProductCollection(), $dataArray);
                $this->forceBackorderCurrentScope($this->productHelper->getProductCollection());
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            } finally{
                $this->positionRegistry->unregister('products_position');
                $this->logger->debug('PRODUCTS STOCK/PRICE/BACKORDER SYNCING DONE! UNSET products_position.');
            }
        }
    }

    /**
     * calculate quantity for product from EXO - changed from protected to public
     * @param \SimpleXMLElement $exoProductData
     * @return number
     */
    public function getQty($exoProductData)
    {
    	return $this->getCalculatedQty($exoProductData);
    }

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
     * Set the force_backorder product attribute with store_id = 0
     * Since Magento looks for that product attribute as well where store_id = 0 on catalog_product_entity_int
     * As a "fallback" value
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection[] $productCollection,
     * @param array $dataArray
     * @todo include exo_due_date saving here as well
     * @return void
     */
    private function setExoAttributesUnderDefaultScope($productCollection, $dataArray)
    {
         $this->logger->debug('setExoAttributesUnderDefaultScope :: Stock Price sync :: PROCESSING...');
         $this->logger->debug('setExoAttributesUnderDefaultScope :: Stock Price sync :: array_keys of dataArray param...');
         $this->logger->debug(print_r(array_keys($dataArray), true));
         if (!empty($productCollection)) {
             foreach ($productCollection as $product) {
                 $resource = $product->getResource();
                 $product->setStoreId(0);
                 $product->setData('force_backorder', 0);
                 $resource->saveAttribute($product, 'force_backorder');

                 if (isset($this->forceBackorderProductData[$product->getSku()])
                     && $this->forceBackorderProductData[$product->getSku()] == '1') {

                     $product->setStoreId(1);
                     $product->setData('status', Status::STATUS_DISABLED);
                     $resource->saveAttribute($product, 'status');

                     $product->setStoreId(4);
                     $product->setData('status', Status::STATUS_DISABLED);
                     $resource->saveAttribute($product, 'status');
                 }

                 // SAVE EXO DUE DATE AS WELL
                 $this->logger->debug('setExoAttributesUnderDefaultScope :: Stock Price sync :: CHECK IF REALLY SAVED FOR '.$product->getId());
             }
         }
         $this->logger->debug('setExoAttributesUnderDefaultScope :: Stock Price sync :: DONE PROCESSING...');
    }
     
    private function forceBackorderCurrentScope($productCollection)
    {
        /********** DEBUGGING **********/
        $debugSkus = ['AN007R', 'DRW020', 'SG80', 'SPT005', 'SPT063', 'TEST123', 'WOM72', 'WW85106', 'WW98063', 'WW98123', 'ADS001'];
        /********** ^ DEBUGGING **********/
        /* @var \Magento\Store\Model\Store $currentScopeStore */
        $currentScopeStore = $this->_storeManager->getStore($this->process->getDefaultStoreViewCode());
        $this->logger->debug('forceBackorderCurrentScope :: Stock Price sync :: PROCESSING...');
        if (!empty($productCollection)) {
            foreach ($productCollection as $product) {
                if (!isset($this->forceBackorderProductData[$product->getSku()])) {
                    $this->logger->debug(__('SKIP PROCESSING FOR SKU %1 -- %2', $product->getSku(), __METHOD__));
                    continue;
                }

                $forceBackorder = $this->forceBackorderProductData[$product->getSku()];

                /********** DEBUGGING **********/
                if (in_array($product->getSku(), $debugSkus)) {
                    $this->logger->debug("********** DEBUGGING **********");
                    $this->logger->debug(__('SKU :: %1 :: %2 PREVOUS force_backorder :: %3 :: NEW force_backorder :: %4', 
                        $product->getSku(), 
                        __METHOD__,
                        $product->getForceBackorder(), 
                        $forceBackorder));
                    $this->logger->debug("********** ^ DEBUGGING **********");
                }
                /********** ^ DEBUGGING **********/

                /** @var \Magento\Catalog\Model\Product $product */
                $resource = $product->getResource();
                $product->setStoreId($currentScopeStore->getStoreId());
                $product->setData('force_backorder', $forceBackorder);
                $resource->saveAttribute($product, 'force_backorder');
                $this->logger->debug('forceBackorderCurrentScope :: Stock price sync :: CHECK IF REALLY SAVED FOR '.$product->getId());
            }
        }
        $this->logger->debug('forceBackorderCurrentScope :: Stock price sync :: DONE PROCESSING...');

    }

    /**
  	 * Return store code of reference website
     * Overrides Acidgreen\Exo\Model\Import\Product\StockPrice::getStoreViewCode()
  	 * @return string
  	 */
  	 private function getStoreViewCode()
   	 {
        return $this->process->getDefaultStoreViewCode();
   	 }

    /**
     * Finish Import
     */
    protected function finishImport()
    {
        //record end time
        $this->endTime = microtime(true);

        $executionTime = ($this->endTime - $this->startTime ) / 60;

        $this->logger->debug('finish Import. Execution Time: ' . $executionTime);
        $this->logger->debug('Reindexing to be done by Cron...');
        // Let cron do the reindexing...
    }
}
