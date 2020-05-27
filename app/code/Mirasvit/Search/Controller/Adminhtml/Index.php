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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\Search\Api\Service\IndexServiceInterface;

abstract class Index extends Action
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var IndexRepositoryInterface
     */
    protected $indexRepository;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    public function __construct(
        Context $context,
        IndexRepositoryInterface $scoreRuleRepository,
        ForwardFactory $resultForwardFactory
    ) {
        $this->context = $context;
        $this->indexRepository = $scoreRuleRepository;
        $this->session = $context->getSession();
        $this->resultForwardFactory = $resultForwardFactory;

        parent::__construct($context);
    }

    /**
     * Initialize page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Search::search');

        $resultPage->getConfig()->getTitle()->prepend(__('Search Indexes'));

        return $resultPage;
    }

    /**
     * @return IndexInterface
     */
    protected function initModel()
    {
        $model = $this->indexRepository->create();

        if ($this->getRequest()->getParam(IndexInterface::ID)) {
            $model = $this->indexRepository->get($this->getRequest()->getParam(IndexInterface::ID));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Search::search_index');
    }
}
