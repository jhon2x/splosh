<?php

namespace Acidgreen\SploshExo\Helper\Import\Product;


use Magento\Catalog\Model\Product\Gallery\GalleryManagement;
use Magento\Catalog\Model\Product\Gallery\GalleryManagementFactory;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Gallery\Processor as GalleryProcessor;
use Magento\Catalog\Model\ResourceModel\Product\Gallery as GalleryResourceModel;

class Images
{
	/**
	 * @var GalleryManagementFactory
	 */
	protected $galleryManagementFactory;
	
	/**
	 * @var GalleryManagement
	 */
	protected $gallery;
	
	/**
	 * @var GalleryResourceModel
	 */
	protected $galleryResourceModel;
	
	/**
	 * @var GalleryProcessor
	 */
	protected $galleryProcessor;
	
	/**
	 * @var StoreManagerInterface
	 */
	protected $storeManager;
	
	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

    /**
     * @var ProductRepository
     */
    protected $productRepository;
    
    /**
     * @var ProductCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;
	
	/**
	 * 
	 */
	public function __construct(
		GalleryManagementFactory $galleryMangementFactory,
		GalleryProcessor $galleryProcessor,
		GalleryResourceModel $galleryResourceModel,
    	ProductCollectionFactory $collectionFactory,
    	ProductRepository $productRepository,
        ConfigHelper $configHelper,
		StoreManagerInterface $storeManager,
		\Psr\Log\LoggerInterface $logger
	) {
		$this->gallery = $galleryMangementFactory->create();
		$this->galleryProcessor = $galleryProcessor;
		$this->galleryResourceModel = $galleryResourceModel;
		$this->collectionFactory = $collectionFactory;
		$this->productRepository = $productRepository;
		$this->configHelper = $configHelper;
		$this->storeManager = $storeManager;
		$this->logger = $logger;
	}
	
	public function removeAllImagesBeforeSync()
	{
		$this->logger->debug(__METHOD__);
		
        // unset media gallery entities here?
        $collection = $this->collectionFactory->create();
        $collection->setOrder('sku', 'asc');
        //$collection->addAttributeToSelect('*');

        $this->logger->debug(__METHOD__.' :: executed. Product collection count :: '.$collection->count());
        
        // don't do it for all for now...
        foreach ($collection as $product) {
        	
        	
        	$product->load('media_gallery');
            $mediaGalleryEntries = $product->getMediaGalleryEntries();
            $sku = $product->getSku();
            
            if (!empty($mediaGalleryEntries)) {
                foreach ($mediaGalleryEntries as $key => $gallery) {
                	\Zend_Debug::dump($gallery->getData());
                	$entryId = $gallery->getId();
                    $gallery->setData('removed', 1);
                    try {
                    	
                    	$this->storeManager->setCurrentStore($product->getStoreId());
                    	
                    	
                    	$this->galleryProcessor->removeImage($product, $gallery['file']);
                    	
                    	$mediaGallery = $product->getMediaGallery();

                    	$productStoreIds = $product->getStoreIds();
                    	// add 'root' store_id
                    	$productStoreIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
                    	foreach ($productStoreIds as $sid) {
                    		$product
                    		->setStoreId($sid)
                    		->setMediaGallery($mediaGallery)
                    		->save();
                    	}
                    	$this->logger->debug(__METHOD__.' :: PLEASE verify thru Magento Admin if '.$sku.' images were deleted.');
                    } catch (\Exception $e) {
                    	$this->logger->debug(__('%1 :: ERROR REMOVING IMAGE FOR %2 :: %3', __METHOD__, $sku, $e->getMessage()));
                    	$this->logger->debug($e->getTraceAsString());
                    }
                }
            }
        }
	}
}
