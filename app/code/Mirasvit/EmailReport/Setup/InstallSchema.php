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
 * @package   mirasvit/module-email-report
 * @version   2.0.8
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Block\Adminhtml\Trigger;
use Mirasvit\EmailReport\Api\Data\ClickInterface;
use Mirasvit\EmailReport\Api\Data\EmailInterface;
use Mirasvit\EmailReport\Api\Data\OpenInterface;
use Mirasvit\EmailReport\Api\Data\OrderInterface;
use Mirasvit\EmailReport\Api\Data\ReviewInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $tables = [];

        $installer->startSetup();

        $tables[] = $this->createEmailTable($installer);
        $tables[] = $this->createOpenTable($installer);
        $tables[] = $this->createClickTable($installer);
        $tables[] = $this->createOrderTable($installer);
        $tables[] = $this->createReviewTable($installer);

        foreach ($tables as $table) {
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }

    /**
     * Create 'mst_emailreport_email' table
     *
     * @param SchemaSetupInterface $installer
     *
     * @return \Magento\Framework\DB\Ddl\Table
     * @throws \Zend_Db_Exception
     */
    protected function createEmailTable(SchemaSetupInterface $installer)
    {
        return $installer->getConnection()->newTable(
            $installer->getTable(EmailInterface::TABLE_NAME)
        )->addColumn(
            EmailInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Email ID'
        )->addColumn(
            TriggerInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Trigger ID'
        )->addColumn(
            QueueInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Queue ID'
        )->addColumn(
            EmailInterface::CREATED_AT,
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Created At'
        )->addForeignKey(
            $installer->getFkName(
                EmailInterface::TABLE_NAME,
                TriggerInterface::ID,
                TriggerInterface::TABLE_NAME,
                TriggerInterface::ID
            ),
            TriggerInterface::ID,
            $installer->getTable(TriggerInterface::TABLE_NAME),
            TriggerInterface::ID,
            Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName(EmailInterface::TABLE_NAME, [QueueInterface::ID]),
            [QueueInterface::ID]
        );
    }

    /**
     * Create 'mst_emailreport_open' table
     *
     * @param SchemaSetupInterface $installer
     *
     * @return \Magento\Framework\DB\Ddl\Table
     * @throws \Zend_Db_Exception
     */
    protected function createOpenTable(SchemaSetupInterface $installer)
    {
        return $installer->getConnection()->newTable(
            $installer->getTable(OpenInterface::TABLE_NAME)
        )->addColumn(
            OpenInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Open ID'
        )->addColumn(
            TriggerInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Trigger ID'
        )->addColumn(
            QueueInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Queue ID'
        )->addColumn(
            OpenInterface::SESSION_ID,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Session ID'
        )->addColumn(
            OpenInterface::CREATED_AT,
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Created At'
        )->addForeignKey(
            $installer->getFkName(
                OpenInterface::TABLE_NAME,
                TriggerInterface::ID,
                TriggerInterface::TABLE_NAME,
                TriggerInterface::ID
            ),
            TriggerInterface::ID,
            $installer->getTable(TriggerInterface::TABLE_NAME),
            TriggerInterface::ID,
            Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName(EmailInterface::TABLE_NAME, [QueueInterface::ID]),
            [QueueInterface::ID]
        );
    }

    /**
     * Create 'mst_emailreport_click' table
     *
     * @param SchemaSetupInterface $installer
     *
     * @return \Magento\Framework\DB\Ddl\Table
     * @throws \Zend_Db_Exception
     */
    protected function createClickTable(SchemaSetupInterface $installer)
    {
        return $installer->getConnection()->newTable(
            $installer->getTable(ClickInterface::TABLE_NAME)
        )->addColumn(
            ClickInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Click ID'
        )->addColumn(
            TriggerInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Trigger ID'
        )->addColumn(
            QueueInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Queue ID'
        )->addColumn(
            ClickInterface::SESSION_ID,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Session ID'
        )->addColumn(
            ClickInterface::CREATED_AT,
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Created At'
        )->addForeignKey(
            $installer->getFkName(
                ClickInterface::TABLE_NAME,
                TriggerInterface::ID,
                TriggerInterface::TABLE_NAME,
                TriggerInterface::ID
            ),
            TriggerInterface::ID,
            $installer->getTable(TriggerInterface::TABLE_NAME),
            TriggerInterface::ID,
            Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName(EmailInterface::TABLE_NAME, [QueueInterface::ID]),
            [QueueInterface::ID]
        );
    }

    /**
     * Create 'mst_emailreport_order' table
     *
     * @param SchemaSetupInterface $installer
     *
     * @return \Magento\Framework\DB\Ddl\Table
     * @throws \Zend_Db_Exception
     */
    protected function createOrderTable(SchemaSetupInterface $installer)
    {
        return $installer->getConnection()->newTable(
            $installer->getTable(OrderInterface::TABLE_NAME)
        )->addColumn(
            OrderInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Order ID'
        )->addColumn(
            TriggerInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Trigger ID'
        )->addColumn(
            QueueInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Queue ID'
        )->addColumn(
            OrderInterface::PARENT_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Parent ID'
        )->addColumn(
            OrderInterface::CREATED_AT,
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Created At'
        )->addForeignKey(
            $installer->getFkName(
                OrderInterface::TABLE_NAME,
                TriggerInterface::ID,
                TriggerInterface::TABLE_NAME,
                TriggerInterface::ID
            ),
            TriggerInterface::ID,
            $installer->getTable(TriggerInterface::TABLE_NAME),
            TriggerInterface::ID,
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                OrderInterface::TABLE_NAME,
                OrderInterface::PARENT_ID,
                'sales_order',
                'entity_id'
            ),
            OrderInterface::PARENT_ID,
            $installer->getTable('sales_order'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName(EmailInterface::TABLE_NAME, [QueueInterface::ID]),
            [QueueInterface::ID]
        );
    }

    /**
     * Create 'mst_emailreport_review' table
     *
     * @param SchemaSetupInterface $installer
     *
     * @return \Magento\Framework\DB\Ddl\Table
     * @throws \Zend_Db_Exception
     */
    protected function createReviewTable(SchemaSetupInterface $installer)
    {
        return $installer->getConnection()->newTable(
            $installer->getTable(ReviewInterface::TABLE_NAME)
        )->addColumn(
            ReviewInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Review ID'
        )->addColumn(
            TriggerInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Trigger ID'
        )->addColumn(
            QueueInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Queue ID'
        )->addColumn(
            ReviewInterface::PARENT_ID,
            Table::TYPE_BIGINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Parent ID'
        )->addColumn(
            ReviewInterface::CREATED_AT,
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Created At'
        )->addForeignKey(
            $installer->getFkName(
                ReviewInterface::TABLE_NAME,
                TriggerInterface::ID,
                TriggerInterface::TABLE_NAME,
                TriggerInterface::ID
            ),
            TriggerInterface::ID,
            $installer->getTable(TriggerInterface::TABLE_NAME),
            TriggerInterface::ID,
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                ReviewInterface::TABLE_NAME,
                ReviewInterface::PARENT_ID,
                'review',
                'review_id'
            ),
            ReviewInterface::PARENT_ID,
            $installer->getTable('review'),
            'review_id',
            Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName(EmailInterface::TABLE_NAME, [QueueInterface::ID]),
            [QueueInterface::ID]
        );
    }
}
