<?php

namespace Acidgreen\SploshExo\Model\ResourceModel;

class SploshCustomerGroup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected function _construct()
	{
		$this->_init('splosh_customer_group', 'id');
	}
}
