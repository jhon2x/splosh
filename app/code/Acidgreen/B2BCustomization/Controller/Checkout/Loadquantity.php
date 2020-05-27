<?php

namespace Acidgreen\B2BCustomization\Controller\Checkout;

class Loadquantity extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Acidgreen\B2BCustomization\Model\CartItem
     */
    protected $cartItem;

    public function __construct(
        \Acidgreen\B2BCustomization\Model\CartItem $cartItem,
		\Magento\Framework\App\Action\Context $context,
        array $data = []
    ) {
        $this->cartItem = $cartItem;
        parent::__construct($context);
    }

    public function execute()
    {
        $json = [];
        if (!empty($this->getRequest()->getParam('product'))) {
            $productId = $this->getRequest()->getParam('product');
            $qty = $this->cartItem->getCartItemProductQty($productId);
            $json['product_id'] = $productId;
            $json['qty']  = $qty;
        } else {
            $json['error'] = 'No product ID';
        }

        $json = json_encode($json);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($json);
    }
}
