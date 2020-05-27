<?php

namespace Acidgreen\SploshBackorder\Block\Checkout;

use Magento\Checkout\Block\Cart as CoreCartBlock;
use Magento\Catalog\Model\Product;

class Cart extends CoreCartBlock
{
    /**
     * Other product data
     * @var Product
     */
    protected $otherProductData;

    /**
     * Stockzone item data for the cart item
     * @var array
     */
    protected $cartStockzoneItem;

    /**
     * set other product data
     * @param Product $otherProductData
     */
    public function setOtherProductData($otherProductData)
    {
        $this->otherProductData = $otherProductData;
        return $this;
    }

    public function getOtherProductData()
    {
        return $this->otherProductData;
    }

    public function setCartStockzoneItem($cartStockzoneItem)
    {
        $this->cartStockzoneItem = $cartStockzoneItem;
        return $this;
    }

    public function getCartStockzoneItem()
    {
        return $this->cartStockzoneItem;
    }

}
