<?php

namespace Acidgreen\CustomerRestrictions\Plugin;

class ProductCollectionRestrict
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

	public function afterGetProductCollection(\Magento\Catalog\Model\Layer $subject, $result)
	{
		$collection = $result;
		
		if($this->agCheckoutHelper->isSiteB2b()){
			$rangeRestrictions = $this->customerRestrictions->getRestriction("range");

			if($rangeRestrictions && !empty($rangeRestrictions)){
				$collection->addAttributeToFilter("range", [ 
						["nin" => array($rangeRestrictions)],
						["null" => true]
					 ], "left");
			}
		}

		return $collection;
	}
}
