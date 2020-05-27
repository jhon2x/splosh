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
use Mirasvit\Search\Api\Data\StopwordInterface;
use Mirasvit\Search\Api\Repository\StopwordRepositoryInterface;
use Mirasvit\Search\Api\Service\StopwordServiceInterface;

abstract class Stopword extends Action
{
    /**
     * @var StopwordRepositoryInterface
     */
    protected $stopwordRepository;

    /**
     * @var StopwordServiceInterface
     */
    protected $stopwordService;

    /**
     * @var Context
     */
    protected $context;

    public function __construct(
        StopwordRepositoryInterface $stopwordRepository,
        StopwordServiceInterface $stopwordService,
        Context $context
    ) {
        $this->stopwordRepository = $stopwordRepository;
        $this->stopwordService = $stopwordService;
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

        $resultPage->getConfig()->getTitle()->prepend(__('Manage Stopwords'));

        return $resultPage;
    }

    /**
     * @return StopwordInterface
     */
    protected function initModel()
    {
        $model = $this->stopwordRepository->create();

        if ($this->getRequest()->getParam(StopwordInterface::ID)) {
            $model = $this->stopwordRepository->get($this->getRequest()->getParam(StopwordInterface::ID));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Search::search_stopword');
    }
}
