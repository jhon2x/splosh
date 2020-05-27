<?php
/**
 * Copyright © 2016 Sectionio. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sectionio\Metrics\Block\Adminhtml\Report;

use \Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    
    protected function _construct()
    {
        $this->_objectId = 'general_id';
        $this->_blockGroup = 'Sectionio_Metrics';
        $this->_controller = 'adminhtml_report';

        parent::_construct();
    }
    
    /**
     * Retrieve text for header element
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('section.io Site Metrics');
    }

    /**
     * Prepare grid / button(s)
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('back');
        return parent::_prepareLayout();
    }
}
