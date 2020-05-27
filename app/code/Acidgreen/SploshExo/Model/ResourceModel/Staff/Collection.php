<?php

namespace Acidgreen\SploshExo\Model\ResourceModel\Staff;

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
		$model = 'Acidgreen\SploshExo\Model\Staff';
		$resourceModel = 'Acidgreen\SploshExo\Model\ResourceModel\Staff';
		
		$this->_init($model, $resourceModel);
	}
}
