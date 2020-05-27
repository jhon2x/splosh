<?php

namespace CommissionFactory\Tracking\Observer;

class Conversion implements \Magento\Framework\Event\ObserverInterface
{
	protected $_layout;

	public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\View\LayoutInterface $layout, \CommissionFactory\Tracking\Helper\Data $commissionFactoryTrackingData)
	{
		$this->_layout = $layout;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$orderIds = $observer->getEvent()->getOrderIds();

		if (empty($orderIds) || !is_array($orderIds))
		{
			return;
		}

		$block = $this->_layout->getBlock("commissionfactory_tracking_conversion");

		if ($block)
		{
			$block->setOrderIds($orderIds);
		}
	}
}
