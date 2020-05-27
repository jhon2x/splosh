<?php

namespace Acidgreen\CustomerRestrictions\Helper;

class Restrictions
{
	protected $sessionFactory;

	protected $customer;

	public function __construct(
		\Magento\Customer\Model\SessionFactory $sessionFactory,
		\Magento\Customer\Model\Customer $customer
		) {
		$this->sessionFactory = $sessionFactory;
		$this->customer = $customer;
	}

	/**
	 * Get Customer Restrictions
	 *
	 * @param String $type
	 * @return Array $restrictionArray
	 */
	public function getRestriction($type = "range")
	{
		$customerData = null;

		try {
			$customerData = $this->customer->load($this->sessionFactory->create()->getCustomer()->getId());

		} catch (NoSuchEntityException $e) {
			$this->logger->debug("\n\n\n--------------------- No Customer -------------------- \n");

			return false;
		}

		if(isset($customerData)){
			if($type == "range")
				$restrictions = $customerData->getRangeRestrictions();
			elseif($type == "categories")
				$restrictions = $customerData->getCategoryRestrictions();
			else
				return false;

			if(isset($restrictions)){
				$restrictionArray = explode(",", $restrictions);

				return $restrictionArray;
			}
		}

		return false;
	}

}
