<?php

namespace Acidgreen\Catalog\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\View as CoreProductViewBlock;
use Acidgreen\SploshBackorder\Model\Stockzone;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;

/**
 * Product View block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends CoreProductViewBlock
{

    /**
     * @var Stockzone\ItemFactory
     */
    protected $stockzoneItemFactory;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @var DataObject
     */
    protected $stockzoneItem;

    /**
     * @param Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductRepositoryInterface|\Magento\Framework\Pricing\PriceCurrencyInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     * @codingStandardsIgnoreStart
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
    	Stockzone\ItemFactory $stockzoneItemFactory,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        // stockzone item factory here...
        $this->stockzoneItemFactory = $stockzoneItemFactory;
        $this->storeManager = $context->getStoreManager();
        $this->logger = $context->getLogger();
        
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    public function getProductStockzoneItem()
    {
        $product = $this->getProduct();

        // register stockzone item instance
        $registryKey = 'stockzone_item_'.$product->getId().'_'.$this->storeManager->getWebsite()->getId();
        $this->logger->debug(__METHOD__.' :: registry key :: '.$registryKey);
        
        if (!$this->_coreRegistry->registry($registryKey)) {

        	/** \Acidgreen\SploshBackorder\Model\ResourceModel\Stockzone\Item $stockzoneItemResource */
        	$stockzoneItemResource = $this->stockzoneItemFactory->create()->getResource();
        	
        	$stockzoneItem = $stockzoneItemResource->getItemByWebsite(
        		$product->getSku(),
        		$this->_storeManager->getWebsite()->getId());
        	if (!empty($stockzoneItem))
        		$this->_coreRegistry->register($registryKey, $stockzoneItem);
        } else {
        	$this->logger->debug(__METHOD__.' :: stockzone item SET ALREADY!');
        }
        
        return $this->_coreRegistry->registry($registryKey);
    }

    public function isProductBackorder()
    {
    	$this->logger->debug(__METHOD__.' :: is product a backorder product???');
        $this->logger->debug(__METHOD__.' :: '.print_r(gettype($this->_coreRegistry->registry('product')), true));
    	
        $product = $this->getProduct();
        
        $this->stockzoneItem = $this->getProductStockzoneItem();
        
        if (!empty($this->stockzoneItem))
            return ($this->stockzoneItem->getBackorders() != Stock::BACKORDERS_NO);

        return false;

    }

}
