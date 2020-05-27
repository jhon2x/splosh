<?php

namespace CommissionFactory\Tracking\Block;

class Conversion extends \Magento\Framework\View\Element\Template
{
	protected $_commissionFactoryTrackingData;
	protected $_orderIds;
	protected $_salesOrderCollection;

	public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection, \CommissionFactory\Tracking\Helper\Data $commissionFactoryTrackingData, array $data = [ ])
	{
		$this->_commissionFactoryTrackingData = $commissionFactoryTrackingData;
		$this->_salesOrderCollection = $salesOrderCollection;

		parent::__construct($context, $data);
	}

	protected function _toHtml()
	{
		$advertiserId = $this->_commissionFactoryTrackingData->getAdvertiserId();

		if (!$advertiserId)
		{
			return "";
		}

		$orderIds = $this->getOrderIds();

        if (empty($orderIds) || !is_array($orderIds))
		{
            return "";
        }

		$html = "";

		$collection = $this->_salesOrderCollection->create();

		$collection->addFieldToFilter("entity_id", [ "in" => $orderIds ]);

		foreach ($collection as $order)
		{
			$html .= "<script>\n";
			$html .= "    (function(a,b,c){a[b]=a[b]||function(){(a[b].q=a[b].q||[]).push(arguments);};a[c]=a[b];})(window,\"CommissionFactory\",\"cf\");\n";
			$html .= "\n";
			$html .= "    cf(\"set\", \"order\", " . json_encode($order->getIncrementId()) . ");\n";
			$html .= "    cf(\"set\", \"amount\", " . json_encode($order->getSubtotal() + $order->getDiscountAmount()) . ");\n";
			$html .= "    cf(\"set\", \"currency\", " . json_encode($order->getOrderCurrencyCode()) . ");\n";
			$html .= "    cf(\"set\", \"coupon\", " . json_encode($order->getCouponCode()) . ");\n";

			if (!$order->getCustomerIsGuest())
			{
				if (count($this->_salesOrderCollection->create()->addFieldToFilter("customer_id", $order->getCustomerId())) == 1)
				{
					$html .= "    cf(\"set\", \"customer\", \"new\");\n";
				}
				else
				{
					$html .= "    cf(\"set\", \"customer\", \"return\");\n";
				}
			}

			$html .= "\n";

			foreach ($order->getAllVisibleItems() as $item)
			{
				$html .= "    cf(\"add\", \"items\", { \"sku\": " . json_encode($item->getSku()) . ", \"price\": " . json_encode($item->getPrice()) . ", \"quantity\": " . json_encode($item->getQtyOrdered()) . " });\n";
			}

			$html .= "\n";
			$html .= "    cf(\"track\");\n";
			$html .= "</script>\n";
		}

		return $html;
	}
}
