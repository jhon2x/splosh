<?php

namespace Splosh\SalesRep\Controller\Adminhtml\Location;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPage;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPage
     */
    public function __construct(Action\Context $context, PageFactory $resultPage)
    {
        parent::__construct($context);
        $this->resultPage = $resultPage;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultPage = $this->resultPage->create();

        if (empty($id)) {
            $resultPage->getConfig()->getTitle()->prepend(__('New Location Mapping'));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Location Mapping'));
        }

        return $resultPage;
    }
}
