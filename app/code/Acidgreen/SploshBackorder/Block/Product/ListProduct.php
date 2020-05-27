<?php

namespace Acidgreen\SploshBackorder\Block\Product;

use Acidgreen\SploshBackorder\Model\Stockzone;
use Acidgreen\SploshBackorder\Model\StockzoneFactory;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class ListProduct extends \Acidgreen\Catalog\Block\Product\ListProduct
{
    /**
     * @var StockzoneFactory
     */
    protected $stockzoneFactory;

    /**
     * @var Stockzone
     */
    protected $stockzone;

    /**
     * @var Stockzone\ItemFactory
     */
    protected $stockzoneItemFactory;

    public function __construct(
        StockzoneFactory $stockzoneFactory,
        Stockzone\ItemFactory $stockzoneItemFactory,
        \Acidgreen\Checkout\Helper\Cart $agCheckoutHelper,
        \Acidgreen\CustomerRestrictions\Helper\Restrictions $customerRestrictions,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
    ) {
        $this->stockzoneFactory = $stockzoneFactory;
        $this->stockzoneItemFactory = $stockzoneItemFactory;

        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
        $this->initStockzone();
    }

    public function initStockzone()
    {
        if (!isset($this->stockzone))
            $this->stockzone = $this->_getStockzone();
    }

    public function getBackorderProducts()
    {
        $productCollection = $this->_getProductCollection();

        /** @var \Acidgreen\SploshBackorder\Model\ResourceModel\Stockzone\Item\Collection $stockzoneItemCollection */
        $stockzoneItemCollection = $this->stockzoneItemFactory->create()->getCollection();

        $skuParams = [];
        $backorderProducts = [];

        // get splosh_inventory_stockzone_item where sku in (array) and splosh_stockzone_id = current stockzone id
        foreach ($productCollection as $product) {
            $skuParams[] = $product->getSku();
        }
        $stockzoneItemCollection->addFieldToFilter('backorders', ['neq' => 0]);
        $stockzoneItemCollection->addFieldToFilter('splosh_stockzone_id', $this->stockzone->getId());
        $stockzoneItemCollection->addFieldToFilter('sku', ['in' => $skuParams]);
        $stockzoneItemCollection->load(false, true);

        foreach ($stockzoneItemCollection as $item) {
            $backorderProducts[$item->getSku()] = $item;
        }

        return $backorderProducts;
    }

    private function _getStockzone()
    {
        /** @var \Acidgreen\SploshBackorder\Model\ResourceModel\Stockzone\Collection $collection */
        $collection = $this->stockzoneFactory->create()->getCollection();
        $collection->addFieldToFilter('website_id', $this->_storeManager->getWebsite()->getId());

        if (count($collection) > 0) {
            return $collection->getFirstItem();
        }

        return false;
    }

    protected function _getProductCollection()
    {
        return parent::_getProductCollection()->addAttributeToSelect(['exo_due_date', 'force_backorder']);
    }

    public function getOtherProductDataCollection()
    {
        $productCollection = $this->_getProductCollection();

        return [];
    }
}
