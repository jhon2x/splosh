<?php

namespace Acidgreen\SploshExo\Helper\Import\Product;

use Acidgreen\Exo\Model\Import\Product as ImportModel;
use Magento\Framework\File\Csv as CsvProcessor; 
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Indexer\Model\IndexerFactory;
use Psr\Log\LoggerInterface;

class AdditionalImages
{
	/**
	 * @var ImportModel
	 */
	protected $importModel;
	
	/**
	 * @var CsvProcessor
	 */
	protected $fileCsv;
	
	/**
	 * @var DirectoryList
	 */
	protected $dir;
	
	/**
	 * @var ProductCollectionFactory
	 */
	protected $collectionFactory;

    /**
     * @var IndexerFactory
     */
    protected $indexerFactory;
	
	/**
	 * @var LoggerInterface
	 */
	protected $logger;
	
	public function __construct(
		ImportModel $importModel,
		CsvProcessor $fileCsv,
		DirectoryList $dir,
		ProductCollectionFactory $collectionFactory,
        IndexerFactory $indexerFactory,
		LoggerInterface $logger
	) {
		$this->importModel = $importModel;
		$this->fileCsv = $fileCsv;
		$this->dir = $dir;
		$this->collectionFactory = $collectionFactory;
		$this->indexerFactory = $indexerFactory;
		$this->logger = $logger;
	}
	
	/**
	 * Import the additional images
	 * @return void
	 */
	public function importAdditionalImages()
	{
		// Set data for importModel?
		// Set CSV file the importModel should look for...
		// Simulate import
		
		try {
			$sourceDir = $this->dir->getPath('media') . '/product_images';
			$fileName = 'ADDITIONAL_IMAGES.csv';
			$sourceFile = $sourceDir.'/'.$fileName;
			
			
			$products = [];
			if (file_exists($sourceFile)) {
				$dataArray = $this->fileCsv->getData($sourceFile);
				$productsBySku = $this->getProductsBySku();
				$numRows = count($dataArray);

				for ($i = 1; $i < $numRows; $i++) {
					// check for special characters, skip if special characters were found
					if (preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬]/', $dataArray[$i][1])) {
						continue;
					}
					// check if product exists first
					if (isset($productsBySku[$dataArray[$i][0]])) {
						$products[] = [
								'sku' => $dataArray[$i][0],
								'additional_images' => $dataArray[$i][1]
						];
						$this->logger->debug(__METHOD__.' :: ADDED PRODUCT WITH SKU '.$dataArray[$i][0].' to the array of products to import.');
					}
				}
			}
			$this->importModel->setImportImagesFileDir('pub/media/product_images');
			$this->importModel->processImport($products);
			$this->logger->debug($this->importModel->getLogTrace());

            $this->triggerReindex();
			
			
		} catch (\Exception $e) {
			$this->logger->debug(__METHOD__.' :: ERROR processing additional images :: '.$e->getMessage());
			$this->logger->debug($e->getTraceAsString());
		}
		
	}
	
	private function getImportDataConfig()
	{
		$data = [
			'entity' => 'catalog_product',
			'behavior' => 'append',
			'validation_strategy' => 'validation-stop-on-errors',
			'allowed_error_count' => 10,
			'import_field_separator' => ',',
			'import_multiple_value_separator' => ',',
			'import_images_file_dir' => '/pub/media/product_images'
		];
		return $data;
	}
	
	public function getProductsBySku()
	{
		$collection = $this->collectionFactory->create();
		$collection->setFlag('has_stock_status_filter', false);
		
		$productsBySku = [];
		
		foreach ($collection as $product) {
			$productsBySku[$product->getSku()] = $product;
		}
		
		$this->logger->debug('getProductsBySku ARRAY LENGTH : '.count($productsBySku));
		return $productsBySku;
	}

    private function triggerReindex()
    {
        $indexers = [];
        $indexerIds = $this->getIndexerIds();

        foreach ($indexerIds as $indexerId) {
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId);
            $this->logger->debug(__('%1 :: indexerId : %2 :: reindexAll()', __METHOD__, $indexerId));
            $indexers[] = $indexer;
        }

        for ($i = 0; $i < count($indexers); $i++) {
            $i->reindexAll();
        }
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
}
