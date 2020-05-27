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



namespace Mirasvit\Mq\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\Mq\Provider\Mysql\Api\Data\QueueInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable(QueueInterface::TABLE_NAME)
        )->addColumn(
            QueueInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            QueueInterface::ID
        )->addColumn(
            QueueInterface::QUEUE_NAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            QueueInterface::QUEUE_NAME
        )->addColumn(
            QueueInterface::BODY,
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => false],
            QueueInterface::BODY
        )->addColumn(
            QueueInterface::STATUS,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            QueueInterface::STATUS
        )->addColumn(
            QueueInterface::RETRIES,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            QueueInterface::RETRIES
        )->addColumn(
            QueueInterface::CREATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            QueueInterface::CREATED_AT
        )->addColumn(
            QueueInterface::UPDATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            QueueInterface::UPDATED_AT
        )->addIndex(
            $installer->getIdxName(QueueInterface::TABLE_NAME, [QueueInterface::QUEUE_NAME]),
            [QueueInterface::QUEUE_NAME]
        )->addIndex(
            $installer->getIdxName(QueueInterface::TABLE_NAME, [QueueInterface::STATUS]),
            [QueueInterface::STATUS]
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
