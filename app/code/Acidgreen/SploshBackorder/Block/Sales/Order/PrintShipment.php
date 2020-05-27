<?php

namespace Acidgreen\SploshBackorder\Block\Sales\Order;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;
use Acidgreen\SploshBackorder\Model\Stockzone\ItemFactory as StockzoneItemFactory;

/**
 * Order information for print
 *
 */
class PrintShipment extends \Magento\Sales\Block\Order\PrintShipment
{

    /**
     * Product IDs
     * @var array
     */
    protected $productIDs;
    
    /**
     * Stockzone item factory
     * @var StockzoneItemFactory
     */
    protected $stockzoneItemFactory;

    /**
     * Product factory class
     * @var ProductFactory
     */
    protected $productFactory;
    
    /**
     * Other product data collection
     * @var Product[]
     */
    protected $otherProductDataCollection;
    
    /**
     * Stockzone items related to orders (esp. backorder products)
     * @var array
     */
    protected $stockzoneItems;
    
    /**
     * Current website ID
     * @var int
     */
    protected $websiteId;

    public function __construct(
        ProductFactory $productFactory,
        StockzoneItemFactory $stockzoneItemFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->stockzoneItemFactory = $stockzoneItemFactory;
        $this->_paymentHelper = $paymentHelper;
        $this->_coreRegistry = $registry;
        $this->addressRenderer = $addressRenderer;

        parent::__construct($context, $registry, $paymentHelper, $addressRenderer, $data);
        $this->websiteId = $this->_storeManager->getWebsite()->getId();
    }

    public function _construct()
    {
        $this->setModuleName('Magento_Sales');
        parent::_construct();
    }
    
    public function getOtherProductDataCollection()
    {
        if (empty($this->otherProductDataCollection)) {
            $otherProductDataCollection = [];
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->productFactory->create()->getCollection();
            $collection->addAttributeToSelect(['exo_due_date'], 'inner')
                ->addFieldToFilter('entity_id', ['in' => $this->getProductIds()]);
            
            foreach ($collection as $product) {
                $otherProductDataCollection[$product->getId()] = $product;
            }
            
            $this->otherProductDataCollection = $otherProductDataCollection;
        }
        
        return $this->otherProductDataCollection;
    }

    public function getStockzoneItems()
    {
        if (empty($this->stockzoneItems)) {
            // load it here.. keyed by product ID/SKU
            $this->stockzoneItems = [];
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->getOrder();
            
            $websiteId = $order->getStore()->getWebsiteId();
        
            /** @var \Acidgreen\SploshBackorder\Model\ResourceModel\Stockzone\Item $stockzoneItemResource */
            $stockzoneItemResource = $this->stockzoneItemFactory->create()->getResource();
            
            $stockzoneItems = $stockzoneItemResource->getCartItems($this->getProductIds(), $websiteId);

            $this->stockzoneItems = $stockzoneItems;
        }
        return $this->stockzoneItems;
    }

    private function getProductIds()
    {
        if (empty($this->productIds)) {
            $items = $this->getOrder()->getAllItems();

            $productIds = [];

            foreach ($items as $item) {
                array_push($productIds, $item->getProductId());
            }
            $this->productIds = $productIds;
        }

        return $this->productIds;
    }
}
