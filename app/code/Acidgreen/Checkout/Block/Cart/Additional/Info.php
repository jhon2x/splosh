<?php

namespace Acidgreen\Checkout\Block\Cart\Additional;

use Magento\Checkout\Block\Cart\Additional\Info as CartAdditionalInfoBlock;
use Magento\Framework\View\Element\Template\Context;
use Psr\Log\LoggerInterface;
use Acidgreen\SploshBackorder\Model\Stockzone;
use Magento\Catalog\Model\Product;

class Info extends CartAdditionalInfoBlock
{
    /**
     * @var array
     */
    protected $cartStockzoneItem;

    /**
     * @var Product
     */
    protected $otherProductData;

    /**
     * set other product data
     * @param Product $product
     * @return void
     */
    public function setOtherProductData($product)
    {
    	$this->otherProductData = $product;
        return $this;
    }

    /**
     * return other product data model
     * @return Product
     */
    public function getOtherProductData()
    {
        return $this->otherProductData;
    }

    /**
     * Set stockzoneItem for block rendering/logic
     * @param Stockzone\Item $stockzoneItem
     */
    public function setCartStockzoneItem($cartStockzoneItem)
    {
        $this->cartStockzoneItem = $cartStockzoneItem;
        return $this;
    }

    /**
     * @return array
     */
    public function getCartStockzoneItem()
    {
        return $this->cartStockzoneItem;
    }
}
