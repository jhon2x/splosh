<?php

namespace Acidgreen\CustomerRestrictions\Plugin;

class ItemCollectionRestrict
{
	protected $agCheckoutHelper;

	protected $customerRestrictions;

	protected $logger;

	public function __construct(
		\Acidgreen\Checkout\Helper\Cart $agCheckoutHelper,
		\Acidgreen\CustomerRestrictions\Helper\Restrictions $customerRestrictions,
		\Psr\Log\LoggerInterface $LoggerInterface
		) {
		$this->agCheckoutHelper = $agCheckoutHelper;
		$this->customerRestrictions = $customerRestrictions;
		$this->logger = $LoggerInterface;
	}

	/**
     * Retrieve Catalog Product List Items
     *
     * @return array
     */
    public function afterGetItemCollection(\Magento\TargetRule\Block\Product\AbstractProduct $subject, $result)
    {
		$collection = $result;

		if($this->agCheckoutHelper->isSiteB2b()){
			$rangeRestrictions = $this->customerRestrictions->getRestriction("range");

			if($rangeRestrictions && !empty($rangeRestrictions)){
				$filtered_ranges = array();

				foreach ($collection as $item) {
					if(!in_array($item->getRange(), $rangeRestrictions))
						$filtered_ranges[] = $item;
				}

				return $filtered_ranges;
			}
		}

		return $collection;
    }
}
