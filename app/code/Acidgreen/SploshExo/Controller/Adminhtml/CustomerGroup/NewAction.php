<?php

namespace Acidgreen\SploshExo\Controller\Adminhtml\CustomerGroup;

class NewAction extends \Magento\Customer\Controller\Adminhtml\Group
{
    /**
     * Edit or create customer group mapping.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Acidgreen_SploshExo::customergroup');
        $resultPage->getConfig()->getTitle()->prepend(__('Splosh Customer Groups'));
        $resultPage->addBreadcrumb(__('Splosh'), __('Splosh'));
        $resultPage->addBreadcrumb(__('Customer Groups'), __('Customer Groups'), $this->getUrl('acidgreen_sploshexo/customergroup'));

        $resultPage->addBreadcrumb(__('New Group Mapping'), __('New Customer Groups Mapping'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Customer Group Mapping'));

        $resultPage->getLayout()->addBlock('Acidgreen\SploshExo\Block\Adminhtml\Customergroup\Edit', 'group', 'content')
            ->setEditMode(false);

        return $resultPage;
    }
}
