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
 * @package   mirasvit/module-search-report
 * @version   1.0.5
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchReport\Controller\Adminhtml\Report;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use \Mirasvit\Report\Api\Repository\ReportRepositoryInterface;

class View extends Action
{
    /**
     * @var ReportRepositoryInterface
     */
    protected $repository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Context
     */
    protected $context;


    public function __construct(
        ReportRepositoryInterface $repository,
        Registry $registry,
        Context $context
    ) {
        $this->repository = $repository;
        $this->registry = $registry;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Search::search');
        $resultPage->getConfig()->getTitle()->prepend(__('Search'));
        $resultPage->getConfig()->getTitle()->prepend(__('Reports'));
        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $report = $this->getRequest()->getParam('report');
        if (!$report) {
            $report = 'search_report_volume';
        }

        $this->registry->register('current_report', $this->repository->get($report));

        /** @var \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->initPage($resultPage);

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_SearchReport::search_report');
    }
}