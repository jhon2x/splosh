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

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Event\Api\Data\EventInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class MassValidate extends \Magento\Backend\App\Action
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;
    /**
     * @var TriggerRepositoryInterface
     */
    private $triggerRepository;

    /**
     * MassValidate constructor.
     *
     * @param EventRepositoryInterface   $eventRepository
     * @param TriggerRepositoryInterface $triggerRepository
     * @param Action\Context             $context
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        TriggerRepositoryInterface $triggerRepository,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->eventRepository = $eventRepository;
        $this->triggerRepository = $triggerRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $eventIds = $this->getRequest()->getParam(EventInterface::ID);
        $triggerId = $this->getRequest()->getParam(TriggerInterface::ID);

        if (!is_array($eventIds)) {
            $this->messageManager->addErrorMessage(__('Please select event(s)'));
        } else {
            $passed = 0;
            $failed = 0;
            try {
                foreach ($eventIds as $eventId) {
                    $event = $this->eventRepository->get($eventId);
                    if ($event) {
                        $trigger = $this->triggerRepository->get($triggerId);
                        try {
                            if ($trigger->validateRules($event->getParams(), true)) {
                                $passed++;
                                $this->messageManager->addSuccessMessage(
                                    __('The event ID %1 passed validation', $eventId)
                                );
                            } else {
                                $failed++;
                                $this->messageManager->addSuccessMessage(
                                    __('The event ID %1 failed validation', $eventId)
                                );
                            }
                        } catch (\Exception $e) {
                            if (strpos($e->getMessage(), 'Undefined index') !== false) {
                                $this->messageManager->addErrorMessage(
                                    __(
                                        'Selected event (ID: %1) is not compatible with the trigger "%2".'
                                        . ' Using this trigger you can validate events with the code "%3"',
                                        $eventId,
                                        $trigger->getTitle(),
                                        $trigger->getEvent()
                                    )
                                );
                            } else {
                                throw $e;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
            $this->messageManager->addSuccessMessage(
                __(
                    'Total of %1 event(s) were validated. Passed: %2, failed: %3',
                    count($eventIds),
                    $passed,
                    $failed
                )
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
