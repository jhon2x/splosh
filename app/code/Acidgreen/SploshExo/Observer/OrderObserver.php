<?php

namespace Acidgreen\SploshExo\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Acidgreen\SploshExo\Helper\Order as OrderHelper;
use Acidgreen\SploshExo\Helper\Records\Tosync as ResyncHelper;
use Psr\Log\LoggerInterface as Logger;

class OrderObserver implements ObserverInterface
{
	/**
     * @var \Acidgreen\Exo\Helper\Order
     */
	protected $orderHelper;

	/**
	 * @var ResyncHelper
	 */
	protected $resyncHelper;

	/**
	 * @var StoreManagerInterface
	 */
	protected $storeManager;

	/**
     * @var \Psr\Log\LoggerInterface
     */
	protected $logger;

	/**
     * __construct
     *
     * @param OrderHelper $orderHelper
     * @param Logger $logger
     */
	public function __construct(
		OrderHelper $orderHelper,
		ResyncHelper $resyncHelper,
		StoreManagerInterface $storeManager,
		Logger $logger)
	{

		$this->orderHelper = $orderHelper;
		$this->resyncHelper = $resyncHelper;
		$this->storeManager = $storeManager;
		$this->logger = $logger;
	}


	public function execute(\Magento\Framework\Event\Observer $observer)
	{

		if(!$this->orderHelper->isExoSalesOrderCreationEnabled()) {
			return;
		}

		try {

			$orderData = $observer->getEvent()->getOrder();

			$exoCreatedOrderId = $this->orderHelper->sendExoSalesOrder($orderData);

			if($exoCreatedOrderId) {

				$orderData->setExoOrderId($exoCreatedOrderId)->save();

				/* \Magento\Sales\Model\ResourceModel\Grid::refresh(); */

			} else {
                // Queue for update later here...
                // resync should be after?
                // $website = $this->storeManager->getWebsite()->getCode();
				// $this->resyncHelper->queueForSync($orderData->getId(), $website, 'order_create');

				$this->logger->debug('AX Order Error. No ID returned');

				throw new \Magento\Framework\Validator\Exception(__(' AX Order Error. No ID returned'));
			}

		} catch (\Exception $e) {

            $this->logger->debug($e->getMessage());
        }




	}
}

?>
