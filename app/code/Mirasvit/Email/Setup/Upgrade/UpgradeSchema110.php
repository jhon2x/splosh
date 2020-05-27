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



namespace Mirasvit\Email\Setup\Upgrade;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\Email\Api\Data\Campaign\StoreInterface;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;

class UpgradeSchema110 implements UpgradeSchemaInterface, VersionableInterface
{
    const VERSION = '1.1.0';

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * {@inheritDoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();

        $this->installCampaignTable($setup);

        $this->modifyTables($setup, $connection);

        $this->modifyTriggerTable($setup, $connection);
    }

    /**
     * Create mst_email_campaign table.
     *
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function installCampaignTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(CampaignInterface::TABLE_NAME)
        )->addColumn(
            CampaignInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Campaign Id'
        )->addColumn(
            CampaignInterface::TITLE,
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Title'
        )->addColumn(
            CampaignInterface::DESCRIPTION,
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Description'
        )->addColumn(
            CampaignInterface::IS_ACTIVE,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Is Active'
        )->addColumn(
            CampaignInterface::UPDATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addColumn(
            CampaignInterface::CREATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        );

        $setup->getConnection()->createTable($table);
    }

    /**
     * Modify all tables/columns from previous installations.
     *
     * @param SchemaSetupInterface $setup
     * @param AdapterInterface $connection
     */
    private function modifyTables(SchemaSetupInterface $setup, AdapterInterface $connection)
    {
        // mst_email_trigger
        $connection->dropColumn($setup->getTable(TriggerInterface::TABLE_NAME), 'run_rule_id');
        $connection->dropColumn($setup->getTable(TriggerInterface::TABLE_NAME), 'stop_rule_id');

        // mst_email_rule
        $connection->dropTable('mst_email_rule');
    }

    /**
     * Modify all trigger table.
     *
     * @param SchemaSetupInterface $setup
     * @param AdapterInterface $connection
     */
    private function modifyTriggerTable(SchemaSetupInterface $setup, AdapterInterface $connection)
    {
        $connection->addColumn(
            $setup->getTable(TriggerInterface::TABLE_NAME),
            CampaignInterface::ID,
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => false,
                'unsigned' => true,
                'comment'  => 'Campaign ID',
                'after'    => TriggerInterface::DESCRIPTION,
            ]
        );
    }
}
