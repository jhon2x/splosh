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


namespace Mirasvit\Email\Model\Trigger;

use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\QueueRepositoryInterface;
use Mirasvit\Email\Api\Service\EventManagementInterface;
use Mirasvit\Email\Api\Service\TriggerHandlerInterface;
use Mirasvit\Email\Model\Queue;
use Mirasvit\Email\Model\QueueFactory;
use Mirasvit\Event\Api\Data\EventInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;
use Mirasvit\Email\Model\Trigger;

class Handler implements TriggerHandlerInterface
{
    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var QueueRepositoryInterface
     */
    protected $queueRepository;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;
    /**
     * @var EventManagementInterface
     */
    private $eventManagement;

    /**
     * Constructor
     *
     * @param EventManagementInterface $eventManagement
     * @param EventRepositoryInterface $eventRepository
     * @param QueueRepositoryInterface $queueRepository
     * @param QueueFactory             $queueFactory
     */
    public function __construct(
        EventManagementInterface $eventManagement,
        EventRepositoryInterface $eventRepository,
        QueueRepositoryInterface $queueRepository,
        QueueFactory $queueFactory
    ) {
        $this->eventRepository= $eventRepository;
        $this->queueRepository = $queueRepository;
        $this->queueFactory = $queueFactory;
        $this->eventManagement = $eventManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function handleEvents(TriggerInterface $trigger, $events = null)
    {
        if (!$events) {
            $events = $this->eventRepository->getCollection()
                ->addFieldToFilter(EventInterface::IDENTIFIER, ['in' => $trigger->getEvents()])
                ->addFieldToFilter(EventInterface::KEY, ['nlike' => 'test_%']) // ignore test events
                ->setOrder(EventInterface::CREATED_AT, 'asc');

            $this->eventManagement->addNewFilter($events, $trigger->getId(), $trigger->getStoreIds());
        }

        /** @var EventInterface $event */
        foreach ($events as $event) {
            $this->handleEvent($trigger, $event);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handleEvent(TriggerInterface $trigger, EventInterface $event)
    {
        $this->processEvent($trigger, $event);
        $this->eventManagement->addProcessedTriggerId($event->getId(), $trigger->getId());

        return $this;
    }

    /**
     * Handle one event
     *
     * @param TriggerInterface $trigger
     * @param EventInterface   $event
     * @return $this
     */
    protected function processEvent(TriggerInterface $trigger, EventInterface $event)
    {
        if (in_array($event->getIdentifier(), $trigger->getCancellationEvents())) {
            $this->cancelTrigger($trigger, $event);
        }

        if (in_array($event->getIdentifier(), $trigger->getTriggeringEvents())) {
            $this->triggerEvent($trigger, $event);
        }

        return $this;
    }

    /**
     * Cancel trigger by event
     *
     * @param TriggerInterface $trigger
     * @param EventInterface   $event
     * @return $this
     */
    protected function cancelTrigger(TriggerInterface $trigger, EventInterface $event)
    {
        $params = $event->getParams();

        $recipientEmail = $params['customer_email'];

        if ($trigger->getIsAdmin()) {
            $recipientEmail = $trigger->getAdminEmail();
        }

        $queueCollection = $this->queueRepository->getCollection();
        $queueCollection->addFieldToFilter(QueueInterface::STATUS, ['neq' => QueueInterface::STATUS_SENT])
            ->addFieldToFilter(TriggerInterface::ID, $trigger->getId())
            ->addFieldToFilter(QueueInterface::RECIPIENT_EMAIL, $recipientEmail);

        foreach ($queueCollection as $queue) {
            $queue->cancel(__('Cancellation Event (%1 - %2)', $event->getKey(), $event->getIdentifier()));
        }

        return $this;
    }

    /**
     * Trigger trigger by event
     *
     * @param TriggerInterface $trigger
     * @param EventInterface   $event
     *
     * @return bool
     */
    public function triggerEvent(TriggerInterface $trigger, EventInterface $event)
    {
        if (!$trigger->validateRules($event->getParams())) {
            return false;
        }

        /** @var \Mirasvit\Email\Model\Trigger\Chain $chain */
        foreach ($trigger->getChainCollection() as $chain) {
            $queue = $this->enqueue($trigger, $chain, $event);

            if (!$queue) {
                continue;
            }

            if ($event->getParam('force')) {
                $queue->send();
            }
        }

        return true;
    }

    /**
     * Add new message to queue.
     *
     * @param TriggerInterface      $trigger
     * @param ChainInterface $chain
     * @param EventInterface        $event
     *
     * @return bool|QueueInterface
     */
    public function enqueue(TriggerInterface $trigger, ChainInterface $chain, EventInterface $event)
    {
        $uniqueKey = "{$event->getKey()}|{$trigger->getId()}|{$chain->getId()}";
        $params = $event->getParams();

        // Calculate scheduled at date starting from event registration date
        if (isset($params['is_test'])) {
            $scheduledAt = date('Y-m-d H:i:s', strtotime($params['created_at']));
        } else {
            $scheduledAt = date('Y-m-d H:i:s', $chain->getScheduledAt(strtotime($event->getCreatedAt())));
        }

        // If there are still queued messages - do not queue another one, ignore it
        $queueCollection = $this->getQueueCollection($trigger->getId(), $chain->getId(), $uniqueKey, $scheduledAt);
        if ($queueCollection->count() != 0) {
            return false;
        }

        // Enqueue message
        $recipientEmail = $params['customer_email'];
        $recipientName = $params['customer_name'];
        if ($trigger->getIsAdmin() && !isset($params['force'])) {
            $recipientEmail = $trigger->getAdminEmail();
            $recipientName = "Administrator";
        }

        if (!$recipientEmail) {
            return false;
        }

        $queue = $this->queueRepository->create();
        $queue->setTriggerId($trigger->getId())
            ->setChainId($chain->getId())
            ->setUniqKey($uniqueKey)
            ->setSenderEmail($trigger->getSenderEmail($params['store_id']))
            ->setSenderName($trigger->getSenderName($params['store_id']))
            ->setRecipientEmail($recipientEmail)
            ->setRecipientName($recipientName)
            ->setArgs($params)
            ->setScheduledAt($scheduledAt);

        $this->queueRepository->save($queue);

        return $queue;
    }

    /**
     * Get queue collection filtered by prepared params.
     *
     * @param int    $triggerId
     * @param int    $chainId
     * @param string $uniqueKey
     * @param string $gmtScheduledAt
     *
     * @return \Mirasvit\Email\Api\Data\QueueInterface[]|\Mirasvit\Email\Model\ResourceModel\Queue\Collection
     */
    private function getQueueCollection($triggerId, $chainId, $uniqueKey, $gmtScheduledAt)
    {
        $queueCollection = $this->queueRepository->getCollection();
        $queueCollection->addFieldToFilter(TriggerInterface::ID, $triggerId)
            ->addFieldToFilter(ChainInterface::ID, $chainId)
            ->addFieldToFilter(QueueInterface::UNIQUE_KEY, $uniqueKey)
            ->addFieldToFilter(QueueInterface::SCHEDULED_AT, $gmtScheduledAt);

        return $queueCollection;
    }
}
