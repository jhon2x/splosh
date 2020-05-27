<?php


namespace Acidgreen\SploshBox\Controller\Adminhtml;

abstract class Box extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Acidgreen_SploshBox::top_level';
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Acidgreen::top_level')
            ->addBreadcrumb(__('Acidgreen'), __('Acidgreen'))
            ->addBreadcrumb(__('Box'), __('Box'));
        return $resultPage;
    }
}
