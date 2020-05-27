<?php

namespace Splosh\SalesRep\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Splosh\SalesRep\Helper\Structure;

class InstallSchema implements InstallSchemaInterface
{
    const STAFF_ADDRESS_MAPPING_TABLE = 'splosh_staff_location_mapping';

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        if ($connection->isTableExists(self::STAFF_ADDRESS_MAPPING_TABLE)) {
            $connection->dropTable(self::STAFF_ADDRESS_MAPPING_TABLE);
        }

        $table = $connection->newTable($setup->getTable(self::STAFF_ADDRESS_MAPPING_TABLE))
            ->addColumn(
                Structure::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Mapping Id'
            )->addColumn(
                Structure::STAFF_ID,
                Table::TYPE_INTEGER,
                10,
                [
                    'nullable' => false
                ],
                'Sales Rep/Staff Id'
            )->addColumn(
                Structure::STATE,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'State'
            )->addColumn(
                Structure::POSTCODE,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Postcode'
            )->addColumn(
                Structure::SUBURB,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Suburb'
            )->setComment('Staff assigned location mapping');

        $connection->createTable($table);

        $setup->endSetup();
    }
}