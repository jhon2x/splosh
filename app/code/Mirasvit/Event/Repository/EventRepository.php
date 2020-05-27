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
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Repository;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Event\Api\Data\Event\InstanceEventInterface;
use Mirasvit\Event\Api\Data\EventInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;
use Mirasvit\Event\Model\ResourceModel\Event\CollectionFactory;
use Mirasvit\Event\Api\Data\EventInterfaceFactory;
use Mirasvit\Event\Service\Config\Map;
use Mirasvit\Event\Service\TimeService;
use Mirasvit\Event\Api\Service\EventKeeperInterface;

class EventRepository implements EventRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var InstanceEventInterface[]
     */
    private $eventPool;

    /**
     * @var TimeService
     */
    private $timeService;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var EventKeeperInterface[]
     */
    private $eventKeepers;

    public function __construct(
        Map $map,
        ResourceConnection $resourceConnection,
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        EventInterfaceFactory $eventFactory,
        TimeService $timeService,
        ObjectManagerInterface $objectManager,
        array $events = [],
        array $eventKeepers = []
    ) {
        $this->entityManager = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->eventFactory = $eventFactory;
        $this->timeService = $timeService;
        $this->objectManager = $objectManager;
        $this->eventPool = $events;
        $this->resourceConnection = $resourceConnection;
        $this->eventKeepers = $eventKeepers;
        $map->loadEvents($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->eventFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $event = $this->create();
        $event = $this->entityManager->load($event, $id);

        if (!$event->getId()) {
            return false;
        }

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function save(EventInterface $event)
    {
        $event->setParamsSerialized(\Zend_Json_Encoder::encode($event->getParams()));

        return $this->entityManager->save($event);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(EventInterface $event)
    {
        $this->entityManager->delete($event);
    }

    /**
     * {@inheritdoc}
     */
    public function register($identifier, $key, $params)
    {
        if (!$this->canRegister($identifier)) {
            return false;
        }

        if (!isset($params[InstanceEventInterface::PARAM_EXPIRE_AFTER])) {
            $params[InstanceEventInterface::PARAM_EXPIRE_AFTER] = 365 * 24 * 60 * 60;
        }

        $storeId = isset($params[InstanceEventInterface::PARAM_STORE_ID])
            ? $params[InstanceEventInterface::PARAM_STORE_ID]
            : 0;

        $createdAt = isset($params[InstanceEventInterface::PARAM_CREATED_AT])
            ? $params[InstanceEventInterface::PARAM_CREATED_AT]
            : null;

        $key = implode('|', $key);

        $gmtExpireAt = $this->timeService->shiftDateTime($params[InstanceEventInterface::PARAM_EXPIRE_AFTER]);

        $collection = $this->getCollection();
        $collection->addFieldToFilter(EventInterface::KEY, $key)
            ->addFieldToFilter(EventInterface::IDENTIFIER, $identifier)
            ->addFieldToFilter(EventInterface::CREATED_AT, ['gt' => $gmtExpireAt]);

        if ($collection->getSize()) {
            return false;
        }

        $event = $this->create();

        $event->setIdentifier($identifier)
            ->setStoreId($storeId)
            ->setKey($key)
            ->setCreatedAt($createdAt)
            ->setParams($params);

        $this->save($event);

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function addEvent($event)
    {
        $this->eventPool[] = $event;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        $events = [];

        foreach ($this->eventPool as $class) {
            try {
                /** @var InstanceEventInterface $event */
                $event = $this->objectManager->create($class);
                if ($event->isActive()) {
                    $events[] = $this->objectManager->create($class);
                }
            } catch (\Exception $e) {
                //based on run mode(cli, browser, updates) - some events weren't available
            }
        }

        return $events;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($identifier)
    {
        foreach ($this->getEvents() as $instance) {
            foreach ($instance->getEvents() as $id => $label) {
                if ($id == $identifier) {
                    return $instance;
                }
            }
        }

        return false;
    }

    /**
     * Whether the event can be registered or not.
     *
     * @param $identifier
     *
     * @return bool
     */
    protected function canRegister($identifier)
    {
        // Check table existence. On Module Installation step the event table does not exist yet.
        if (!$this->resourceConnection->getConnection()->isTableExists(
            $this->resourceConnection->getTableName(EventInterface::TABLE_NAME)
        )) {
            return false;
        }

        foreach ($this->eventKeepers as $eventKeeper) {
            if (in_array($identifier, $eventKeeper->getEvents())) {
                return true;
            }
        }

        return false;
    }
}
