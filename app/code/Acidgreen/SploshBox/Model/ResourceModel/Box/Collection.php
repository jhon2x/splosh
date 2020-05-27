<?php


namespace Acidgreen\SploshBox\Model\ResourceModel\Box;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

	/**
	 * 
	 * @var string
	 */
	protected $_idFieldName = 'box_id';
	
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Acidgreen\SploshBox\Model\Box',
            'Acidgreen\SploshBox\Model\ResourceModel\Box'
        );
    }
}
