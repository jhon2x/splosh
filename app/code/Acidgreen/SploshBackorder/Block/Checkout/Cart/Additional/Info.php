<?php

namespace Acidgreen\SploshBackorder\Block\Checkout\Cart\Additional;

use Acidgreen\Checkout\Block\Cart\Additional\Info as CartAdditionalInfoParentBlock;
use Magento\Framework\View\Element\Template\Context;
use Acidgreen\SploshBackorder\Model\Stockzone;
use Magento\Catalog\Model\Product;

class Info extends CartAdditionalInfoParentBlock
{
	/**
	 * @var array
	 */
	protected $cartStockzoneItem;
	
    /**
     * Other product data
     * @var Product
     */
    protected $otherProductData;

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

    public function getProductName()
    {
        return $this->getItem()->getProduct()->getName();
    }

    public function getItemDueDate()
    {
        $product = $this->getItem()->getProduct();
        return $product->getExoDueDate();
    }
}
