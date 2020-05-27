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



namespace Mirasvit\Email\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_email_event')
        )->addColumn(
            'event_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Event Id'
        )->addColumn(
            'uniq_key',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => false],
            'Uniq Key'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Code'
        )->addColumn(
            'args_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Args Serialized'
        )->addColumn(
            'processed',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Processed'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addColumn(
            'store_ids',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Store Ids'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_email_event_trigger')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Id'
        )->addColumn(
            'event_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Event Id'
        )->addColumn(
            'trigger_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Trigger Id'
        )->addColumn(
            'status',
            Table::TYPE_TEXT,
            10,
            ['unsigned' => false, 'nullable' => false],
            'Status'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_email_queue')
        )->addColumn(
            'queue_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Queue Id'
        )->addColumn(
            'status',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Status'
        )->addColumn(
            'trigger_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Trigger Id'
        )->addColumn(
            'chain_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Chain Id'
        )->addColumn(
            'uniq_key',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Uniq Key'
        )->addColumn(
            'uniq_hash',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Uniq Hash'
        )->addColumn(
            'scheduled_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Scheduled At'
        )->addColumn(
            'sent_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Sent At'
        )->addColumn(
            'attemtps_number',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Attemtps Number'
        )->addColumn(
            'sender_email',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Sender Email'
        )->addColumn(
            'sender_name',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Sender Name'
        )->addColumn(
            'recipient_email',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Recipient Email'
        )->addColumn(
            'recipient_name',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Recipient Name'
        )->addColumn(
            'subject',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Subject'
        )->addColumn(
            'content',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Content'
        )->addColumn(
            'args_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Args Serialized'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addColumn(
            'history',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'History'
        )->addIndex(
            $installer->getIdxName('mst_email_queue', ['status']),
            ['status']
        )->addIndex(
            $installer->getIdxName('mst_email_queue', ['scheduled_at']),
            ['scheduled_at']
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_email_rule')
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Title'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Description'
        )->addColumn(
            'is_active',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Is Active'
        )->addColumn(
            'is_system',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Is System'
        )->addColumn(
            'conditions_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => false],
            'Conditions Serialized'
        )->addColumn(
            'actions_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => false],
            'Actions Serialized'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_email_trigger')
        )->addColumn(
            'trigger_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Trigger Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Title'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Description'
        )->addColumn(
            'store_ids',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Store Ids'
        )->addColumn(
            'is_active',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Is Active'
        )->addColumn(
            'active_from',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Active From'
        )->addColumn(
            'active_to',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Active To'
        )->addColumn(
            'trigger_type',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Trigger Type'
        )->addColumn(
            'event',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Event'
        )->addColumn(
            'cancellation_event',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Cancellation Event'
        )->addColumn(
            'schedule',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Schedule'
        )->addColumn(
            'run_rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Run Rule Id'
        )->addColumn(
            'stop_rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Stop Rule Id'
        )->addColumn(
            TriggerInterface::RULE_SERIALIZED,
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Rule Conditions Serialized'
        )->addColumn(
            'sender_email',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Sender Email'
        )->addColumn(
            'sender_name',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Sender Name'
        )->addColumn(
            'copy_email',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Copy Email'
        )->addColumn(
            'ga_source',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ga Source'
        )->addColumn(
            'ga_medium',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ga Medium'
        )->addColumn(
            'ga_term',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ga Term'
        )->addColumn(
            'ga_content',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ga Content'
        )->addColumn(
            'ga_name',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ga Name'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_email_trigger_chain')
        )->addColumn(
            'chain_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Chain Id'
        )->addColumn(
            'trigger_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Trigger Id'
        )->addColumn(
            'delay',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Delay'
        )->addColumn(
            'template_id',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Template Id'
        )->addColumn(
            'run_rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Run Rule Id'
        )->addColumn(
            'stop_rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Stop Rule Id'
        )->addColumn(
            'coupon_enabled',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Coupon Enabled'
        )->addColumn(
            'coupon_sales_rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Coupon Sales Rule Id'
        )->addColumn(
            'coupon_expires_days',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Coupon Expires Days'
        )->addColumn(
            'cross_sells_enabled',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Cross Sells Enabled'
        )->addColumn(
            'cross_sells_type_id',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Cross Sells Type Id'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_email_unsubscription')
        )->addColumn(
            'unsubscription_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Unsubscription Id'
        )->addColumn(
            'email',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Email'
        )->addColumn(
            'trigger_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Trigger Id'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        );
        $installer->getConnection()->createTable($table);
    }
}
