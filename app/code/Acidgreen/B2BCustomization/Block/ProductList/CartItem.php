<?php

namespace Acidgreen\B2BCustomization\Block\ProductList;

class CartItem extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Acidgreen\B2BCustomization\Model\CartItem
     */
    protected $cartItem;

    /**
     * @var array
     */
    protected $_cartItems;

    /**
     * @var int
     */
    protected $productId;

    public function __construct(
        \Acidgreen\B2BCustomization\Model\CartItem $cartItem,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->cartItem = $cartItem;
        parent::__construct($context);
    }

    /**
     * Check if product is in the cart
     * @return boolean true if in cart
     */
    public function isProductInCart($productId)
    {
        if (!$this->_cartItems) {
            $this->_cartItems = $this->cartItem->getCartItemsByProductId();
        }
        return (isset($this->_cartItems[$productId]));
    }

    public function getProductCartQty()
    {
        return ($this->_cartItems[$this->getProductId()]);
    }

    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    public function getProductId()
    {
        return $this->productId;
    }
}
