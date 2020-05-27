<?php


namespace Acidgreen\SploshBox\Model\ResourceModel;

class Box extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('acidgreen_box', 'box_id');
    }
}
