<?php

namespace Acidgreen\SploshBackorder\Model\ResourceModel\Stockzone;

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
		$model = 'Acidgreen\SploshBackorder\Model\Stockzone';
		$resourceModel = 'Acidgreen\SploshBackorder\Model\ResourceModel\Stockzone';
		
		$this->_init($model, $resourceModel);
	}
}
