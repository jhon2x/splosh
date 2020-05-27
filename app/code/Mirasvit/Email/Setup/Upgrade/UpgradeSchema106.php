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
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Data\TriggerEventInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Data\UnsubscriptionInterface;

class UpgradeSchema106 implements UpgradeSchemaInterface, VersionableInterface
{
    const VERSION = '1.0.6';

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

        $this->upgradeTriggerTable($setup, $connection);
        $this->upgradeChainTable($setup, $connection);
        $this->upgradeEventTable($setup, $connection);
        $this->upgradeDates($setup, $connection);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param AdapterInterface $connection
     */
    private function upgradeTriggerTable(SchemaSetupInterface $setup, AdapterInterface $connection)
    {
        if (!$connection->tableColumnExists(
            $setup->getTable($setup->getTable(TriggerInterface::TABLE_NAME)),
            TriggerInterface::RULE_SERIALIZED
        )
        ) {
            $connection->addColumn(
                $setup->getTable($setup->getTable(TriggerInterface::TABLE_NAME)),
                TriggerInterface::RULE_SERIALIZED,
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '64K',
                    'nullable' => true,
                    'comment'  => 'Rule Conditions',
                    'after'    => TriggerInterface::CANCELLATION_EVENT,
                ]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param AdapterInterface $connection
     */
    private function upgradeChainTable(SchemaSetupInterface $setup, AdapterInterface $connection)
    {
        $connection->dropColumn(
            $setup->getTable(ChainInterface::TABLE_NAME),
            ChainInterface::DELAY
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param AdapterInterface $connection
     */
    private function upgradeEventTable(SchemaSetupInterface $setup, AdapterInterface $connection)
    {
        $connection->dropTable('mst_email_event');
    }

    /**
     * Upgrade date columns for extension's tables.
     *
     * @param SchemaSetupInterface $setup
     * @param AdapterInterface $connection
     */
    private function upgradeDates(SchemaSetupInterface $setup, AdapterInterface $connection)
    {
        foreach ([
                     TriggerInterface::TABLE_NAME,
                     QueueInterface::TABLE_NAME,
                     TriggerEventInterface::TABLE_NAME,
                     ChainInterface::TABLE_NAME,
                     UnsubscriptionInterface::TABLE_NAME,
                 ] as $table) {
            foreach (['created_at', 'updated_at'] as $columnName) {
                $connection->modifyColumn(
                    $setup->getTable($table),
                    $columnName,
                    [
                        'type'     => Table::TYPE_TIMESTAMP,
                        'nullable' => false,
                        'default'  => $columnName == 'created_at'
                            ? Table::TIMESTAMP_INIT
                            : Table::TIMESTAMP_INIT_UPDATE,
                    ]
                );
            }
        }
    }
}
