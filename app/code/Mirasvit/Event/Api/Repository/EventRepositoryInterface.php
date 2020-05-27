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



namespace Mirasvit\Event\Api\Repository;

use Mirasvit\Event\Api\Data\Event\InstanceEventInterface;
use Mirasvit\Event\Api\Data\EventInterface;

interface EventRepositoryInterface
{
    /**
     * @return \Mirasvit\Event\Model\ResourceModel\Event\Collection|EventInterface[]
     */
    public function getCollection();

    /**
     * @return EventInterface
     */
    public function create();

    /**
     * @param EventInterface $event
     * @return EventInterface
     */
    public function save(EventInterface $event);

    /**
     * @param int $id
     * @return EventInterface|false
     */
    public function get($id);

    /**
     * @param EventInterface $event
     * @return bool
     */
    public function delete(EventInterface $event);

    /**
     * @param string $identifier
     * @param array $key
     * @param array $params
     * @return EventInterface|false
     */
    public function register($identifier, $key, $params);

    /**
     * Add event to new event to event pool.
     *
     * @param string $event - event class name
     *
     * @return $this
     */
    public function addEvent($event);

    /**
     * @return InstanceEventInterface[]
     */
    public function getEvents();

    /**
     * @param string $identifier
     * @return InstanceEventInterface
     */
    public function getInstance($identifier);
}
