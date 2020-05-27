<?php

namespace Acidgreen\SploshExo\Model\ResourceModel;

class Staff extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected function _construct()
	{
		$this->_init('splosh_staff', 'id');
	}
}
