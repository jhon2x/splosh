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

use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Email\Api\Service\EventManagementInterface;
use Mirasvit\Email\Api\Service\SenderInterface;
use Mirasvit\Email\Api\Service\TriggerHandlerInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;
use Mirasvit\Event\Api\Service\EventServiceInterface;

class TestSender implements SenderInterface
{
    /**
     * @var TriggerRepositoryInterface
     */
    private $triggerRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var EventServiceInterface
     */
    private $eventService;
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;
    /**
     * @var TriggerHandlerInterface
     */
    private $triggerHandler;
    /**
     * @var EventManagementInterface
     */
    private $eventManagement;

    public function __construct(
        EventManagementInterface $eventManagement,
        TriggerHandlerInterface $triggerHandler,
        EventRepositoryInterface $eventRepository,
        EventServiceInterface $eventService,
        StoreManagerInterface $storeManager,
        TriggerRepositoryInterface $triggerRepository
    ) {
        $this->triggerRepository = $triggerRepository;
        $this->storeManager      = $storeManager;
        $this->eventService      = $eventService;
        $this->eventRepository   = $eventRepository;
        $this->triggerHandler    = $triggerHandler;
        $this->eventManagement   = $eventManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function sendChain(ChainInterface $chain, $to)
    {
        $trigger = $this->triggerRepository->get($chain->getTriggerId());

        foreach ($this->getStoreIds($trigger) as $storeId) {
            $event = $this->createEvent($to, $storeId, $trigger->getEvent());

            ini_set('display_errors', 1);

            $queue = $this->triggerHandler->enqueue($trigger, $chain, $event);
            if ($queue) {
                $queue->send();
                $this->eventManagement->addProcessedTriggerId($event->getId(), $trigger->getId());
            }
        }

        return true;
    }

    /**
     * @param TriggerInterface $trigger
     *
     * @return int[]
     */
    private function getStoreIds(TriggerInterface $trigger)
    {
        $storeIds = $trigger->getStoreIds();

        if ($storeIds[0] == 0) {
            unset($storeIds[0]);
            /** @var \Magento\Store\Model\Store $store */
            foreach ($this->storeManager->getStores() as $storeId => $store) {
                if ($store->isActive()) {
                    $storeIds[] = $storeId;
                }
            }
        }

        return $storeIds;
    }

    /**
     * Create test event based on passed parameters.
     *
     * @param string $email
     * @param int    $storeId
     * @param string $event
     *
     * @return \Mirasvit\Event\Api\Data\EventInterface
     */
    private function createEvent($email, $storeId, $event)
    {
        $params = $this->eventService->getRandomParams($storeId);
        $params['force'] = true;
        $params['is_test'] = true;
        $params['customer_email'] = $email;

        $event = $this->eventRepository->create()
            ->setStoreId($storeId)
            ->setIdentifier($event)
            ->setParams($params->getData())
            ->setKey('test_' . time());

        return $this->eventRepository->save($event);
    }
}
