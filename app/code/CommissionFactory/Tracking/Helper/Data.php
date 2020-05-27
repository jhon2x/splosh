<?php

namespace CommissionFactory\Tracking\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	public function getAdvertiserId($store = null)
	{
		return $this->scopeConfig->getValue("commissionfactory/tracking/advertiser", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
	}
}
