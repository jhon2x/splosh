<?php

namespace Acidgreen\CustomerRestrictions\Plugin;

class CategoryRestrict
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

	/**
    * Plugin to filter parent categories based on restriction
    */
	public function afterGetStoreCategories(\Smartwave\Megamenu\Block\Topmenu $subject, $result)
	{
		$storeCategories = $result;
		
		//if b2b site
		if($this->agCheckoutHelper->isSiteB2b()){
			$categoryRestrictions = $this->customerRestrictions->getRestriction("categories");

			if($categoryRestrictions && !empty($categoryRestrictions)){
				$filtered_categories = array();

				if($storeCategories){
					foreach ($storeCategories as $category) {
						if(!in_array($category->getId(), $categoryRestrictions))
							$filtered_categories[] = $category;
					}
				}
				
				return $filtered_categories;
			}
		}

		return $result;
	}

	/**
    * Plugin to filter child categories based on restriction
    */
	public function aroundGetActiveChildCategories(
		\Smartwave\Megamenu\Block\Topmenu $subject, 
		callable $proceed, 
		$category
	) {
		$childCategories = $proceed($category);
		
		//if b2b site
		if($this->agCheckoutHelper->isSiteB2b()){
			$categoryRestrictions = $this->customerRestrictions->getRestriction("categories");

			if($categoryRestrictions && !empty($categoryRestrictions)){
				$filtered_child_categories = array();

				if($childCategories){
					foreach ($childCategories as $category) {
						if(!in_array($category->getId(), $categoryRestrictions))
							$filtered_child_categories[] = $category;
					}
	
					return $filtered_child_categories;
				}
				
			}
		}

		return $childCategories;
	}
}
