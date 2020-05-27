<?php

namespace Acidgreen\SploshBackorder\Block\Order\Email\Items\Order;

use Magento\Sales\Block as CoreSalesBlock;
use Magento\Catalog\Model\Product;

class DefaultOrder extends CoreSalesBlock\Order\Email\Items\Order\DefaultOrder
{
    // set otherProductData and stockzoneItem(s) here...
    /**
     * @var Product
     */
    protected $otherProductData;

    /**
     * @var array
     */
    protected $stockzoneItem;

    /**
     * Set product model for other product data here,
     * rather than doing $item->getProduct() per loop..per loop
     * @param Product
     * @return $this
     */
    public function setOtherProductData(Product $otherProductData)
    {
        $this->otherProductData = $otherProductData;
        return $this;
    }

    /**
     * Other product data (model) accessor
     * @return Product
     */
    public function getOtherProductData()
    {
        return $this->otherProductData;
    }

    /**
     * Set stockzone item data here,
     * @param Product
     * @return $this
     */
    public function setStockzoneItem(Product $stockzoneItem)
    {
        $this->stockzoneItem = $stockzoneItem;
        return $this;
    }

    /**
     * Other product data (model) accessor
     * @return Product
     */
    public function getStockzoneItem()
    {
        return $this->stockzoneItem;
    }

}
