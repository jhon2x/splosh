<?php

namespace Acidgreen\SploshBackorder\Block\Order\Email;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;
use Acidgreen\SploshBackorder\Model\Stockzone\ItemFactory as StockzoneItemFactory;

/**
 * 
 * @todo load stockzone items collection
 *
 */
class Items extends \Magento\Sales\Block\Order\Email\Items
{
    /**
     * Array of product IDs
     * @var array
     */
    protected $productIds;
    
    /**
     * Stockzone item factory
     * @var StockzoneItemFactory
     */
    protected $stockzoneItemFactory;

	/**
	 * Product model factory
	 * @var ProductFactory
	 */
	protected $productFactory;
	
    /**
     * Other product data
     * @var Product
     */
    protected $otherProductData;

    /**
     * Stockzone item data
     * @var array
     */
    protected $stockzoneItem;

	public function __construct(
		ProductFactory $productFactory,
		StockzoneItemFactory $stockzoneItemFactory,
		\Magento\Framework\View\Element\Template\Context $context,
		array $data = []
	) {
		$this->productFactory = $productFactory;
		
		$this->stockzoneItemFactory = $stockzoneItemFactory;
		
		parent::__construct($context, $data);
		
	}
	
    /**
     * Get other product data collection that contains EXO custom attribute/s
     * Rather than doing something like $item->getProduct() every loop..
     * @return array
     */
	public function getOtherProductDataCollection()
	{
		$order = $this->getOrder();
		
		$collection = $this->productFactory->create()->getCollection();
		$collection->addAttributeToSelect(['exo_due_date', 'force_backorder'], 'inner');
        // don't forget the WHERE entity_id IN ($productIds)
        $collection->addFieldToFilter('entity_id', ['in' => $this->getProductIds()]);
		
		$collection->load(false, true);
		
        $otherProductDataCollection = [];
		foreach ($collection as $product) {
            $otherProductDataCollection[$product->getId()] = $product;
		}

        return $otherProductDataCollection;
	}

    /**
     * Get stockzone item collection
     * @return array
     */
    public function getStockzoneItemCollection()
    {
        $stockzoneItemCollection = [];

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getOrder();
        
        $websiteId = $order->getStore()->getWebsiteId();
        
        /** @var \Acidgreen\SploshBackorder\Model\ResourceModel\Stockzone\Item $stockzoneItemResource */
        $stockzoneItemResource = $this->stockzoneItemFactory->create()->getResource();
        
        $stockzoneItemCollection = $stockzoneItemResource->getCartItems($this->getProductIds(), $websiteId);
        
        return $stockzoneItemCollection;
    }

    /**
     * Set otherProductData
     * @param Product $otherProductData
     * @return $this
     */
    public function setOtherProductData($otherProductData)
    {
        $this->otherProductData = $otherProductData;
        return $this;
    }

    /**
     * otherProductData accessor
     * @return Product
     */
    public function getOtherProductData()
    {
        return $this->otherProductData;
    }

    /**
     * Set stockzone item for "Backorder Only" logic 
     * @param array $stockzoneItem
     */
    public function setStockzoneItem($stockzoneItem)
    {
        $this->stockzoneItem = $stockzoneItem;
        return $this;
    }

    /**
     * stockzoneItem accessor
     * @return Product
     */
    public function getStockzoneItem()
    {
        return $this->stockzoneItem;
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
