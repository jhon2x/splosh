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



namespace Mirasvit\Email\Controller\Adminhtml\Event;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Email\Api\Service\EventManagementInterface;
use Mirasvit\Email\Controller\Adminhtml\Event;
use Mirasvit\Email\Model\Trigger\Handler;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class Reset extends Event
{
    /**
     * @var TriggerRepositoryInterface
     */
    protected $triggerRepository;

    /**
     * @var Handler
     */
    protected $handler;
    /**
     * @var EventManagementInterface
     */
    private $eventManagement;

    /**
     * @param EventManagementInterface   $eventManagement
     * @param TriggerRepositoryInterface $triggerRepository
     * @param EventRepositoryInterface   $eventRepository
     * @param Handler                    $handler
     * @param Registry                   $registry
     * @param Context                    $context
     */
    public function __construct(
        EventManagementInterface $eventManagement,
        TriggerRepositoryInterface $triggerRepository,
        EventRepositoryInterface $eventRepository,
        Handler $handler,
        Registry $registry,
        Context $context
    ) {
        $this->eventManagement = $eventManagement;
        $this->triggerRepository = $triggerRepository;
        $this->handler = $handler;

        parent::__construct($eventRepository, $registry, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $event = $this->initModel();

        if ($event->getId()) {
            $this->eventManagement->removeProcessedTriggers($event->getId());

            $triggers = $this->triggerRepository->getCollection()->addActiveFilter();
            foreach ($triggers as $trigger) {
                $this->handler->handleEvents($trigger, [$event]);
            }

            $this->messageManager->addSuccessMessage(__('Done.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
