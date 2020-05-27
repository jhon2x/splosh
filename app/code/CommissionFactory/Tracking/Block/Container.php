<?php

namespace CommissionFactory\Tracking\Block;

class Container extends \Magento\Framework\View\Element\Template
{
	protected $_commissionFactoryTrackingData;

	public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection, \CommissionFactory\Tracking\Helper\Data $commissionFactoryTrackingData, array $data = [ ])
	{
		$this->_commissionFactoryTrackingData = $commissionFactoryTrackingData;

		parent::__construct($context, $data);
	}

	protected function _toHtml()
	{
		$advertiserId = $this->_commissionFactoryTrackingData->getAdvertiserId();

		if (!$advertiserId)
		{
			return "";
		}

		return "<script async src=\"https://t.cfjump.com/tag/" . htmlspecialchars($advertiserId) . "\"></script>\n";
	}
}
