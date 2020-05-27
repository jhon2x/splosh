<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Container;

class Event extends Container
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlManager;

    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->urlManager = $context->getUrlBuilder();

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_event';
        $this->_blockGroup = 'Mirasvit_Email';
        $this->_headerText = __('Event Log');

        parent::_construct();

        $this->buttonList->remove('add');

        $this->buttonList->add('fetch', [
            'label'   => __('Fetch New Events'),
            'onclick' => "window.location.href='" . $this->urlManager->getUrl('*/*/fetch') . "'",
            'class'   => 'secondary',
        ], -100);

        return $this;
    }
}
