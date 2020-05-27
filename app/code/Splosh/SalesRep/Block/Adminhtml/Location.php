<?php

namespace Splosh\SalesRep\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Location extends Container
{
    /**
     * Set Grid Details
     */
    protected function _construct()
    {
        $this->_controller = 'splosh_salesrep';
        $this->_headerText = 'Manage Staff Location';
        $this->_addButtonLabel = 'Map New Staff Location';
        parent::_construct();
    }
}