<?php

namespace Acidgreen\Checkout\Block\Checkout\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor as CheckoutLayoutProcessor;
use Psr\Log\LoggerInterface;

class LayoutProcessor
{
	public function afterProcess(CheckoutLayoutProcessor $layoutProcessor, $jsLayout)
	{
        /**
         * SPL-352
         * If you need a logger, use \Zend\Log\Logger instead
         */
		$paymentForms = &$jsLayout['components']['checkout']['children']['steps']['children']
			['billing-step']['children']
			['payment']['children']['payments-list']['children'];
		
		foreach ($paymentForms as $pf => $element) {
			if (isset($element['children']['form-fields']) && isset($element['children']['form-fields']['children'])) {
				$paymentForms[$pf]['children']['form-fields']['children']['postcode']['validation']['validate-digits'] = true;
				$paymentForms[$pf]['children']['form-fields']['children']['postcode']['validation']['max_text_length'] = 4;
			}
		}
		
		return $jsLayout;
	}
}
