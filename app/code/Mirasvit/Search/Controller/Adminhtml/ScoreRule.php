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
use Magento\Framework\Registry;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\Search\Api\Repository\ScoreRuleRepositoryInterface;
use Mirasvit\Search\Api\Service\IndexServiceInterface;
use Mirasvit\Search\Service\ScoreRuleService;

abstract class ScoreRule extends Action
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ScoreRuleRepositoryInterface
     */
    protected $scoreRuleRepository;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    public function __construct(
        ScoreRuleRepositoryInterface $scoreRuleRepository,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        Context $context
    ) {
        $this->scoreRuleRepository = $scoreRuleRepository;
        $this->registry = $registry;
        $this->resultForwardFactory = $resultForwardFactory;

        $this->context = $context;
        $this->session = $context->getSession();

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

        $resultPage->getConfig()->getTitle()->prepend(__('Score Rules'));

        return $resultPage;
    }

    /**
     * @return ScoreRuleInterface
     */
    protected function initModel()
    {
        $model = $this->scoreRuleRepository->create();

        if ($this->getRequest()->getParam(ScoreRuleInterface::ID)) {
            $model = $this->scoreRuleRepository->get($this->getRequest()->getParam(ScoreRuleInterface::ID));
        }

        $this->registry->register(ScoreRuleInterface::class, $model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Search::search_score_rule');
    }
}
