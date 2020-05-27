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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\Event\Api\Data\EventInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

abstract class Event extends Action
{
    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Context
     */
    protected $context;

    /**
     * Constructor
     *
     * @param EventRepositoryInterface $eventRepository
     * @param Registry                 $registry
     * @param Context                  $context
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        Registry $registry,
        Context $context
    ) {
        $this->eventRepository = $eventRepository;
        $this->registry = $registry;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Backend::marketing');
        $resultPage->getConfig()->getTitle()->prepend(__('Follow Up Email'));
        $resultPage->getConfig()->getTitle()->prepend(__('Events'));

        return $resultPage;
    }

    /**
     * Current event model
     *
     * @return EventInterface
     */
    protected function initModel()
    {
        $model = $this->eventRepository->create();

        if ($this->getRequest()->getParam('id')) {
            $event = $this->eventRepository->get($this->getRequest()->getParam('id'));
            if ($event) {
                $model = $event;
            }
        }

        $this->registry->register('current_model', $model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Email::event');
    }
}
