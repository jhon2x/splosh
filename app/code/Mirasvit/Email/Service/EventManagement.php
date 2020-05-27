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

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Email\Api\Data\TriggerEventInterface;
use Mirasvit\Email\Api\Service\EventManagementInterface;
use Mirasvit\Event\Api\Data\EventInterface;
use Mirasvit\Event\Model\ResourceModel\Event\Collection;

class EventManagement implements EventManagementInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * EventManagement constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function addNewFilter(Collection $collection, $triggerId, $storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds[] = $storeIds;
        }

        $collection->getSelect()
            ->joinLeft(
                ['et' => $collection->getTable(TriggerEventInterface::TABLE_NAME)],
                'et.event_id = main_table.'.EventInterface::ID.' AND et.trigger_id = ' . $triggerId,
                []
            )->where('(et.status = "new" OR et.status IS NULL)');

        if (count($storeIds) && !in_array(0, $storeIds)) {
            $collection->getSelect()
                ->where('(main_table.' . EventInterface::STORE_ID . ' IN (' . implode(',', $storeIds) . ')'
                    . ' OR main_table.' . EventInterface::STORE_ID . ' = 0)');
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function addProcessedTriggerId($eventId, $triggerId)
    {
        $connection = $this->resourceConnection->getConnection();

        $data = [
            'trigger_id' => $triggerId,
            'event_id'   => $eventId,
            'status'     => 'done',
            'created_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
        ];

        $connection->delete(
            $this->resourceConnection->getTableName(TriggerEventInterface::TABLE_NAME),
            'event_id = ' . $eventId . ' AND trigger_id=' . $triggerId
        );

        $connection->insert(
            $this->resourceConnection->getTableName(TriggerEventInterface::TABLE_NAME),
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeProcessedTriggers($eventId)
    {
        $this->resourceConnection->getConnection()->delete(
            $this->resourceConnection->getTableName(TriggerEventInterface::TABLE_NAME),
            'event_id = ' . $eventId
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessedTriggerIds($eventId)
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from($this->resourceConnection->getTableName(TriggerEventInterface::TABLE_NAME))
            ->where('event_id=?', $eventId);

        return $connection->fetchAll($select);
    }
}
