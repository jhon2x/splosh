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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\Email\Api\Data\ChainInterface;

class UpgradeSchema104 implements UpgradeSchemaInterface, VersionableInterface
{
    const VERSION = '1.0.4';

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

        $connection->addColumn(
            $setup->getTable($setup->getTable(ChainInterface::TABLE_NAME)),
            ChainInterface::DAY,
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'unsigned' => true,
                'comment'  => ChainInterface::DAY,
                'after'    => ChainInterface::DELAY,
            ]
        );
        $connection->addColumn(
            $setup->getTable($setup->getTable(ChainInterface::TABLE_NAME)),
            ChainInterface::HOUR,
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'unsigned' => true,
                'comment'  => ChainInterface::HOUR,
                'after'    => ChainInterface::DAY,
            ]
        );
        $connection->addColumn(
            $setup->getTable($setup->getTable(ChainInterface::TABLE_NAME)),
            ChainInterface::MINUTE,
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'unsigned' => true,
                'comment'  => ChainInterface::MINUTE,
                'after'    => ChainInterface::HOUR,
            ]
        );
        $connection->addColumn(
            $setup->getTable($setup->getTable(ChainInterface::TABLE_NAME)),
            ChainInterface::SEND_FROM,
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'unsigned' => true,
                'comment'  => ChainInterface::SEND_FROM,
                'after'    => ChainInterface::MINUTE,
            ]
        );
        $connection->addColumn(
            $setup->getTable($setup->getTable(ChainInterface::TABLE_NAME)),
            ChainInterface::SEND_TO,
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'unsigned' => true,
                'comment'  => ChainInterface::SEND_TO,
                'after'    => ChainInterface::SEND_FROM,
            ]
        );
        $connection->addColumn(
            $setup->getTable($setup->getTable(ChainInterface::TABLE_NAME)),
            ChainInterface::SEND_TO,
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'unsigned' => true,
                'comment'  => ChainInterface::SEND_TO,
                'after'    => ChainInterface::SEND_FROM,
            ]
        );
        $connection->addColumn(
            $setup->getTable($setup->getTable(ChainInterface::TABLE_NAME)),
            ChainInterface::EXCLUDE_DAYS,
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'comment'  => ChainInterface::EXCLUDE_DAYS,
                'after'    => ChainInterface::SEND_TO,
            ]
        );
    }
}
