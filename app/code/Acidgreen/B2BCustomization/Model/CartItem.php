<?php

namespace Acidgreen\B2BCustomization\Model;

class CartItem
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $itemsByProductId;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->itemsByProductId = [];
    }

    public function getCartItemsByProductId()
    {
        // to make sure it's updated as much as possible, we dont if (!$this-<>itemsByProductId this...
        $items = $this->checkoutSession->getQuote()->getAllVisibleItems();

        foreach ($items as $item) {
            $this->itemsByProductId[$item->getProductId()] = $item->getQty();
        }

        return $this->itemsByProductId;
    }

    public function cartItemExists($productId)
    {
        if (!$this->itemsByProductId) {
            $this->itemsByProductId = $this->getCartItemsByProductId();
        }
        return (isset($this->itemsByProductId[$productId]));
    }

    public function getCartItemProductQty($productId)
    {
        if (empty($this->itemsByProductId)) {
            $this->getCartItemsByProductId();
            if (empty($this->itemsByProductId[$productId])) {
                return false;
            }
        }
        return $this->itemsByProductId[$productId];
    }

}
