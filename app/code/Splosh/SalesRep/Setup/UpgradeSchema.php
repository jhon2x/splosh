<?php

namespace Splosh\SalesRep\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Splosh\SalesRep\Helper\Structure;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $table = $setup->getTable(Structure::STAFF_TABLE);

            if ($connection->isTableExists($table)) {
                if (!$connection->tableColumnExists($table, Structure::EXO_STAFF_PHOTO)) {
                    $connection->addColumn($table, Structure::EXO_STAFF_PHOTO, [
                        'TYPE' => Table::TYPE_TEXT,
                        'NULLABLE' => true,
                        'AFTER' => Structure::EXO_STAFF_WEBSITE_ID,
                        'COMMENT' => 'EXO staff photo'
                    ]);
                }
            }
        }

        $setup->endSetup();
    }
}