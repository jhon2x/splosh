<?php

namespace Splosh\SalesRep\Block\Adminhtml;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;

class Edit extends Container
{
    /**
     * Edit constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);

        $this->_objectId = 'id';
        $this->_blockGroup = 'Splosh_SalesRep';
        $this->_controller = 'adminhtml';

        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->update('back', 'onclick', 'setLocation(\'' . $this->getUrl('splosh_salesrep/location/index') . '\')');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        $record_id = $this->getRequest()->getParam('id');

        if ($record_id) {
            return __('Edit Mapping Id: ' . $record_id);
        }
        return __('New Mapping');
    }
}