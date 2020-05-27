<?php

namespace Splosh\SalesRep\Model\ResourceModel\Location;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Declare Model and Resource Model
     */
    public function _construct()
    {
        $this->_init('Splosh\SalesRep\Model\Location', 'Splosh\SalesRep\Model\ResourceModel\Location');
    }
}