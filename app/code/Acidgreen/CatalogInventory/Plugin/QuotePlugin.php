<?php

namespace Acidgreen\CatalogInventory\Plugin;

use Psr\Log\LoggerInterface as Logger;
use Magento\Checkout\Model\Session;

class QuotePlugin
{
  	protected $logger;

  	protected $apiHandler;

  	/**
    * @param Logger $logger,
     */
  	public function __construct(
    		Logger $logger,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Acidgreen\Checkout\Helper\Cart $agCheckoutHelper,
        Session $checkoutSession
    )
  	{
    		$this->logger = $logger;
        $this->stockRegistry = $stockRegistry;
        $this->_agCheckoutHelper = $agCheckoutHelper;
        $this->_checkoutSession = $checkoutSession;
  	}

    /**
  	 * Intercept \Magento\Customer\Model\AccountManagement::createAccount()
  	 * @return Customer Obj
  	 */
    public function aroundAddProduct(
        $subject,
        $proceed,
        \Magento\Catalog\Model\Product $product,
        $request = null,
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ) {

        if ($this->_agCheckoutHelper->isSiteB2b()) {
            $this->logger->debug('Add Product Plugin Condition Triggered');

            if ($request === null) {
                $request = 1;
            }

            $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
            $minimumQty = $stockItem->getMinSaleQty();

            if ($request instanceof \Magento\Framework\DataObject) {
                /**
                 * SPL-303
                 * Just setQty to minimumQty if empty
                 */
                if (empty($request->getQty())) {
                    $this->logger->debug('SPL-303 :: setting a default qty instead of '.$minimumQty);
                    $request->setQty($minimumQty);
                } else {
                    if ($request->getQty() < $minimumQty) {
                        $this->_checkoutSession->setUseNotice(false);
                        return 'Could not add product to cart, minimum quantity to purchase this product is '.$minimumQty;
                        //$request->setQty($minimumQty);
                        //$this->logger->debug('New Request Amount is: '.$request->getQty());
                    }
                }
            } else {
                if ($request < $minimumQty) {
                    $request = $minimumQty;
                    $this->logger->debug('New Request Amount is: '.$request);
                }
            }
        }

        return $proceed($product, $request, $processMode);
    }

}
