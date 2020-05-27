<?php

namespace Acidgreen\CustomerRestrictions\Plugin;

class ProductRestrict
{
	protected $agCheckoutHelper;

	protected $customerRestrictions;

	public function __construct(
		\Acidgreen\Checkout\Helper\Cart $agCheckoutHelper,
		\Acidgreen\CustomerRestrictions\Helper\Restrictions $customerRestrictions
	) {
		$this->agCheckoutHelper = $agCheckoutHelper;
		$this->customerRestrictions = $customerRestrictions;
	}

	public function aroundInitProduct(
		\Magento\Catalog\Helper\Product $subject,
		callable $proceed,
		...$args
	) {
		$product = $proceed(...$args);

		//if b2b site
		if($this->agCheckoutHelper->isSiteB2b()){
			$rangeRestrictions = $this->customerRestrictions->getRestriction("range");

			if($rangeRestrictions && !empty($rangeRestrictions)){
				if($product){
					$range = $product->getRange();

					if($range && (!empty($rangeRestrictions) && in_array($range, $rangeRestrictions)))
						return false;
				}
			}
		}

		return $product;
	}
}
