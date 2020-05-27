<?php

namespace Acidgreen\SploshBackorder\Model\ResourceModel;

class Stockzone extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected function _construct()
	{
		$this->_init('splosh_inventory_stockzone', 'id');
	}
}
