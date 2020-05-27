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
use Mirasvit\Search\Api\Data\SynonymInterface;
use Mirasvit\Search\Api\Repository\SynonymRepositoryInterface;
use Mirasvit\Search\Api\Service\SynonymServiceInterface;

abstract class Synonym extends Action
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var SynonymRepositoryInterface
     */
    protected $synonymRepository;

    /**
     * @var SynonymServiceInterface
     */
    protected $synonymService;

    public function __construct(
        SynonymRepositoryInterface $synonymRepository,
        SynonymServiceInterface $synonymService,
        Context $context
    ) {
        $this->synonymRepository = $synonymRepository;
        $this->synonymService = $synonymService;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Search::search');

        $resultPage->getConfig()->getTitle()->prepend(__('Manage Synonyms'));

        return $resultPage;
    }

    /**
     * @return SynonymInterface
     */
    protected function initModel()
    {
        $model = $this->synonymRepository->create();

        if ($this->getRequest()->getParam(SynonymInterface::ID)) {
            $model = $this->synonymRepository->get($this->getRequest()->getParam(SynonymInterface::ID));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Search::search_synonym');
    }
}
