<?php

namespace Splosh\SalesRep\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Location extends AbstractDb
{

    /**
     * Declare table and id field
     */
    public function _construct()
    {
        $this->_init('splosh_staff_location_mapping', 'id');
    }
}