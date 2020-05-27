<?php

namespace Acidgreen\CatalogInventory\Plugin;

use Psr\Log\LoggerInterface;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;

class Item
{
    /**
     * @var Logger
     */

    public function __construct(
        LoggerInterface $logger,
        \Acidgreen\Checkout\Helper\Cart $agCheckoutHelper
    ) {
        $this->logger = $logger;
        $this->_agCheckoutHelper = $agCheckoutHelper;
    }

    public function afterGetMinSaleQty(StockItem $stockItem, $result)
    {
        if (!$this->_agCheckoutHelper->isSiteB2b()) {
            return 1.0;
        }
        return $result;
    }
}
