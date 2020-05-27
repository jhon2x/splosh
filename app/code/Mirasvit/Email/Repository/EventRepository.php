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



namespace Mirasvit\Email\Repository;

use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Event\Api\Data\EventInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class EventRepository implements EventRepositoryInterface
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var array
     */
    private $eventPool;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        EventRepositoryInterface $eventRepository,
        array $events = []
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventPool = $events;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        $ids = [];
        $collection = $this->eventRepository->getCollection();
        foreach ($this->getEvents() as $event) {
            foreach (array_keys($event->getEvents()) as $identifier) {
                $ids[] = $identifier;
            }
        }

        $collection->addFieldToFilter(EventInterface::IDENTIFIER, ['in' => $ids]);

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->eventRepository->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->eventRepository->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function save(EventInterface $event)
    {
        return $this->eventRepository->save($event);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(EventInterface $event)
    {
        return $this->eventRepository->delete($event);
    }

    /**
     * {@inheritdoc}
     */
    public function register($identifier, $key, $params)
    {
        return $this->eventRepository->register($identifier, $key, $params);
    }

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
            $events[] = $this->objectManager->create($class);
        }

        return $events;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($identifier)
    {
        foreach ($this->getEvents() as $instance) {
            foreach (array_keys($instance->getEvents()) as $id) {
                if ($id == $identifier) {
                    return $instance;
                }
            }
        }

        return false;
    }
}
