<?php
/**
 * Copyright © 2016 Sectionio. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sectionio\Metrics\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;

class FetchInfo extends Action
{
    /** @var \Sectionio\Metrics\Helper\Data $helper */
    protected $helper;

    /**
     * @param Magento\Backend\App\Action\Context $context
     * @param \Sectionio\Metrics\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Sectionio\Metrics\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
    }

    /**
     * Execute account and application api calls
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $this->helper->refreshApplications($this->messageManager);

        return $resultRedirect->setPath('metrics/report/index');
    }
}
