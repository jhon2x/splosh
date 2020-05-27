<?php

namespace Acidgreen\SploshExo\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Acidgreen\SploshExo\Helper\Order as OrderHelper;
use Acidgreen\SploshExo\Helper\Records\Tosync as ResyncHelper;
use Psr\Log\LoggerInterface as Logger;

class OrderPlaceAfterObserver implements ObserverInterface
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
        $this->logger->debug(__METHOD__);

		if(!$this->orderHelper->isExoSalesOrderCreationEnabled()) {
			return;
		}

		try {

			$orderData = $observer->getEvent()->getOrder();
            // use the INCREMENT ID instead! modify the resyncing class to use INCREMENT ID as well
            // $this->logger->debug(__('%1 :: orderData ID :: %2', __METHOD__, print_r($orderData->getId(), true)));

			$orderIncrementId = $orderData->getIncrementId();
            if (!empty($orderIncrementId)) {
                // Queue for update later here...
                // resync should be after?
                $website = $this->storeManager->getWebsite()->getCode();
				$this->resyncHelper->queueForSync($orderIncrementId, $website, 'order_create');
            }

		} catch (\Exception $e) {

            $this->logger->debug($e->getMessage());
        }

    }
}
