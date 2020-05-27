<?php

namespace Acidgreen\SploshExo\Model\ResourceModel\SploshCustomerGroup;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
	/**
	 * ID Field name
	 * @var string
	 */
	protected $_idFieldName = 'id';
	
	protected function _construct()
	{
		$model = 'Acidgreen\SploshExo\Model\SploshCustomerGroup';
		$resourceModel = 'Acidgreen\SploshExo\Model\ResourceModel\SploshCustomerGroup';
		
		$this->_init($model, $resourceModel);
	}
}
