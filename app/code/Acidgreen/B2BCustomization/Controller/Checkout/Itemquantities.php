<?php

namespace Acidgreen\B2BCustomization\Controller\Checkout;

class Itemquantities extends \Magento\Framework\App\Action\Action
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
        $json['products'] = $this->cartItem->getCartItemsByProductId();

        $json = json_encode($json);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($json);
    }
}
