<?php

namespace Acidgreen\SploshExo\Plugin;

use Psr\Log\LoggerInterface as Logger;
use Magento\Store\Model\StoreManagerInterface;
use Acidgreen\SploshExo\Helper\Order as OrderHelper;
use Acidgreen\SploshExo\Helper\Records\Tosync as ResyncHelper;

class CartManagementInterfacePlugin
{
  	protected $logger;

  	/**
     * @param Logger $logger,
     */
  	public function __construct(
        OrderHelper $orderHelper,
    		ResyncHelper $resyncHelper,
    		StoreManagerInterface $storeManager,
    		Logger $logger
    ) {

    		$this->orderHelper = $orderHelper;
    		$this->resyncHelper = $resyncHelper;
    		$this->storeManager = $storeManager;
    		$this->logger = $logger;
  	}

    /**
  	 * Intercept \Magento\Quote\Model\QuoteManagement::submit()
  	 * @return Order Obj
  	 */
    public function aroundSubmit (
        $subject,
        $proceed,
        \Magento\Quote\Model\Quote $quote,
        $orderData = []
    ) {

        try {
            $createdOrder = $proceed($quote, $orderData);
        } catch (\Exception $e) {
            throw $e;
        }

        if(!$this->orderHelper->isExoSalesOrderCreationEnabled() || !$createdOrder) {
        		return $createdOrder;
        }

        try {
        		$orderData = $createdOrder;
        		$exoCreatedOrderId = $this->orderHelper->sendExoSalesOrder($orderData);
        		if($exoCreatedOrderId) {
        				$orderData->setExoOrderId($exoCreatedOrderId)->save();
        		} else {
        				$this->logger->debug('AX Order Error. No ID returned');
        				throw new \Magento\Framework\Validator\Exception(__(' AX Order Error. No ID returned'));
        		}
        } catch (\Exception $e) {
            $this->logger->debug($e->getTraceAsString());
            $this->logger->debug($e->getMessage());
        }

        $this->logger->debug(__METHOD__);

    		try {
    		    $orderIncrementId = $orderData->getIncrementId();
            if (!empty($orderIncrementId)) {
                $website = $this->storeManager->getWebsite()->getCode();
    				    $this->resyncHelper->queueForSync($orderIncrementId, $website, 'order_create');
            }
    		} catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return $createdOrder;
    }
}
