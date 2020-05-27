<?php

namespace Acidgreen\B2BCustomization\Block\Home\BestsellersList;

class Product extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Acidgreen\B2BCustomization\Model\CartItem
     */
    protected $cartItem;

    public function __construct(
        \Acidgreen\B2BCustomization\Model\CartItem $cartItem,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->cartItem = $cartItem;
        parent::__construct($context);
    }

    public function cartItemExists($productId)
    {
        return $this->cartItem->cartItemExists($productId);
    }

    public function getCartItemProductQty($productId)
    {
        return $this->cartItem->getCartItemProductQty($productId);
    }
}
