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
 * @package   mirasvit/module-search-landing
 * @version   1.0.7
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchLanding\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mirasvit\SearchLanding\Api\Data\PageInterface;
use Mirasvit\SearchLanding\Api\Repository\PageRepositoryInterface;

abstract class Page extends Action
{
    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var Context
     */
    protected $context;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        Context $context
    ) {
        $this->pageRepository = $pageRepository;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Backend::system');
        $resultPage->getConfig()->getTitle()->prepend(__('Search'));
        $resultPage->getConfig()->getTitle()->prepend(__('Landing Pages'));

        return $resultPage;
    }

    /**
     * @return PageInterface
     */
    public function initModel()
    {
        $model = $this->pageRepository->create();

        if ($this->getRequest()->getParam(PageInterface::ID)) {
            $model = $this->pageRepository->get($this->getRequest()->getParam(PageInterface::ID));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_SearchLanding::search_landing_page');
    }
}
