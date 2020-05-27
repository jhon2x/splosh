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
 * @package   mirasvit/module-message-queue
 * @version   1.0.9
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Mq\Cron;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\Mq\Provider\Mysql\Api\Data\QueueInterface;

class CleanHistory
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Delete 30 days old messages from queue.
     *
     * @return void
     */
    public function execute()
    {
        $monthAgo   = date('Y-m-d H:i:s', time() - 30 * 24 * 60 * 60);
        $connection = $this->resource->getConnection();
        $tableName  = $this->resource->getTableName(QueueInterface::TABLE_NAME);

        $select = $connection->select()
            ->from($tableName)
            ->where(QueueInterface::CREATED_AT . ' <= ?', $monthAgo);

        $connection->query($connection->deleteFromSelect($select, $tableName));
    }
}
