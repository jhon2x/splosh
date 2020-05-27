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



namespace Mirasvit\Email\Service;

use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Email\Api\Service\EventProcessorInterface;
use Mirasvit\Email\Api\Service\TriggerHandlerInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class EventProcessor implements EventProcessorInterface
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
     * @var TriggerHandler
     */
    private $triggerHandler;

    /**
     * EventProcessor constructor.
     *
     * @param TriggerHandlerInterface    $triggerHandler
     * @param TriggerRepositoryInterface $triggerRepository
     * @param EventRepositoryInterface   $eventRepository
     */
    public function __construct(
        TriggerHandlerInterface $triggerHandler,
        TriggerRepositoryInterface $triggerRepository,
        EventRepositoryInterface $eventRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->triggerRepository = $triggerRepository;
        $this->triggerHandler = $triggerHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($eventId)
    {
        $event = $this->eventRepository->get($eventId);
        if (!$event) {
            return $this;
        }

        foreach ($this->getTriggers($event->getIdentifier(), $event->getStoreId()) as $trigger) {
            $this->triggerHandler->handleEvent($trigger, $event);
        }
    }

    /**
     * Get collection of triggers that can process the event.
     *
     * @param string $eventCode
     * @param int    $storeId
     *
     * @return \Mirasvit\Email\Api\Data\TriggerInterface[]|\Mirasvit\Email\Model\ResourceModel\Trigger\Collection
     */
    private function getTriggers($eventCode, $storeId)
    {
        $triggers = $this->triggerRepository->getCollection();
        $triggers->addActiveFilter();
        $triggers->addFieldToFilter(
            [TriggerInterface::EVENT, TriggerInterface::CANCELLATION_EVENT],
            [
                ['eq' => $eventCode],
                ['finset' => $eventCode]
            ]
        );

        // filter by stores
        foreach ($triggers as $trigger) {
            $storeIds = $trigger->getStoreIds();
            if (count($storeIds)
                && !in_array($storeId, $storeIds)
                && !in_array(0, $storeIds)
            ) {
                $triggers->removeItemByKey($trigger->getId());
            }
        }

        return $triggers;
    }
}
