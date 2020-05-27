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
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;

class UpgradeSchema112 implements UpgradeSchemaInterface, VersionableInterface
{
    const VERSION = '1.1.2';

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

        $this->addFkToQueueTable($setup, $connection);
        $this->addTriggerFkToChainTable($setup, $connection);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param AdapterInterface     $connection
     */
    private function addFkToQueueTable(SchemaSetupInterface $setup, AdapterInterface $connection)
    {
        $connection->addForeignKey(
            $setup->getFkName(
                QueueInterface::TABLE_NAME,
                QueueInterface::TRIGGER_ID,
                TriggerInterface::TABLE_NAME,
                TriggerInterface::ID
            ),
            $setup->getTable(QueueInterface::TABLE_NAME),
            QueueInterface::TRIGGER_ID,
            $setup->getTable(TriggerInterface::TABLE_NAME),
            TriggerInterface::ID,
            Table::ACTION_CASCADE
        );

        $connection->addForeignKey(
            $setup->getFkName(
                QueueInterface::TABLE_NAME,
                QueueInterface::CHAIN_ID,
                ChainInterface::TABLE_NAME,
                ChainInterface::ID
            ),
            $setup->getTable(QueueInterface::TABLE_NAME),
            QueueInterface::CHAIN_ID,
            $setup->getTable(ChainInterface::TABLE_NAME),
            ChainInterface::ID,
            Table::ACTION_CASCADE
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param AdapterInterface     $connection
     */
    private function addTriggerFkToChainTable(SchemaSetupInterface $setup, AdapterInterface $connection)
    {
        $connection->addForeignKey(
            $setup->getFkName(
                ChainInterface::TABLE_NAME,
                TriggerInterface::ID,
                TriggerInterface::TABLE_NAME,
                TriggerInterface::ID
            ),
            $setup->getTable(ChainInterface::TABLE_NAME),
            TriggerInterface::ID,
            $setup->getTable(TriggerInterface::TABLE_NAME),
            TriggerInterface::ID,
            Table::ACTION_CASCADE
        );
    }
}
