<?php

namespace Acidgreen\SploshBackorder\Setup;


use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $connection = $setup->getConnection();

        /**
         * splosh_inventory_stockzone:
         * id
         * stock_id
         * website_id
         * description
         */
        $stockZoneTable = $connection->newTable($setup->getTable('splosh_inventory_stockzone'));

        $stockZoneTable
        	->addColumn('id',
				Ddl\Table::TYPE_INTEGER,
	        	null,
	        	['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
	        	'Entity ID'
        	)->addColumn('stock_id',
        		Ddl\Table::TYPE_INTEGER,
        		null,
        		['nullable' => true, 'unsigned' => true],
        		'Stock ID'
        	)->addColumn('website_id', 
        		Ddl\Table::TYPE_INTEGER,
        		null,
        		['nullable' => false, 'unsigned' => true],
        		'Website ID'
        	)->addColumn('description', 
        		Ddl\Table::TYPE_TEXT,
        		null,
        		[],
        		'Zone Description'
        	); // to be continued
        
        $connection->createTable($stockZoneTable);
        
        /**
         * splosh_inventory_stockzone_item
         * id
         * splosh_stockzone_id
         * cataloginventory_item_id
         * product_id
         * qty
         * backorders
         * use_config_backorders
         * min_qty
         * use_config_min_sale_qty
         * max_qty
         * use_config_max_sale_qty
         * is_in_stock
         * manage_stock
         */
     
        $stockZoneItemTable = $connection->newTable($setup->getTable('splosh_inventory_stockzone_item'));

        $stockZoneItemTable
        	->addColumn('id',
				Ddl\Table::TYPE_INTEGER,
	        	null,
	        	['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
	        	'Entity ID'
            )->addColumn('splosh_stockzone_id',
            	Ddl\Table::TYPE_INTEGER,
            	null,
                ['nullable' => false, 'unsigned' => true],
                'Splosh multi-node Stock ID'
            )->addColumn('cataloginventory_stock_id',
            	Ddl\Table::TYPE_SMALLINT,
            	5,
                ['unsigned' => true],
                'Catalog Stock ID'
            )->addColumn('cataloginventory_item_id',
            	Ddl\Table::TYPE_INTEGER,
            	null,
                ['unsigned' => true],
                'Catalog Stock Item ID'
            )->addColumn('product_id',
            	Ddl\Table::TYPE_INTEGER,
            	null,
                ['nullable' => false, 'unsigned' => true],
                'Product ID'
            )->addColumn('sku',
            	Ddl\Table::TYPE_TEXT,
            	64,
                [],
                'SKU'
            )->addColumn('qty',
            	Ddl\Table::TYPE_DECIMAL,
            	'12,4',
                [],
                'Quantity'
            )->addColumn('backorders',
            	Ddl\Table::TYPE_SMALLINT,
            	5,
                ['nullable' => false, 'unsigned' => true],
                'Backorders'
            )->addColumn('use_config_backorders',
            	Ddl\Table::TYPE_SMALLINT,
            	5,
                ['nullable' => false, 'unsigned' => true],
                'Use Config Backorders'
            )->addColumn('min_qty',
            	Ddl\Table::TYPE_DECIMAL,
            	'12,4',
            	[],
            	'Min Qty'
            )->addColumn('use_config_min_qty',
            	Ddl\Table::TYPE_SMALLINT,
            	5,
            	['nullable' => false, 'unsigned' => true]
            )->addColumn('min_sale_qty',
            	Ddl\Table::TYPE_DECIMAL,
            	'12,4',
            	[],
            	'Min Sale Qty'
            )->addColumn('use_config_min_sale_qty',
            	Ddl\Table::TYPE_SMALLINT,
            	5,
            	['nullable' => false, 'unsigned' => true]
            )->addColumn('max_sale_qty',
            	Ddl\Table::TYPE_DECIMAL,
            	'12,4',
            	[],
            	'Max Sale Qty'
            )->addColumn('use_config_max_sale_qty',
            	Ddl\Table::TYPE_SMALLINT,
            	5,
            	['nullable' => false, 'unsigned' => true]
            )->addColumn('is_in_stock',
            	Ddl\Table::TYPE_SMALLINT,
            	5,
            	['nullable' => false, 'unsigned' => true]
            )->addColumn('manage_stock',
            	Ddl\Table::TYPE_SMALLINT,
            	5,
            	['nullable' => false, 'unsigned' => true]
            )->addColumn('use_config_manage_stock',
            	Ddl\Table::TYPE_SMALLINT,
            	5,
            	['nullable' => false, 'unsigned' => true]
            );

        $connection->createTable($stockZoneItemTable);

        // create table...
        /**
         * splosh_inventory_stockzone_status
         * product_id
         * website_id
         * splosh_stockzone_id
         * cataloginventory_stock_id
         * qty
         * stock_status
         */
        $stockZoneStatusTable = $connection->newTable($setup->getTable('splosh_inventory_stockzone_status'));

            $stockZoneStatusTable
            ->addColumn('product_id',
            	Ddl\Table::TYPE_INTEGER,
            	null,
                ['nullable' => false, 'unsigned' => true],
                'Product ID'
            )->addColumn('website_id',
        		Ddl\Table::TYPE_SMALLINT,
        		5,
        		['nullable' => false, 'unsigned' => true],
        		'Website ID'
            )->addColumn('splosh_stockzone_id',
            	Ddl\Table::TYPE_INTEGER,
            	null,
                ['nullable' => false, 'unsigned' => true],
                'Splosh multi-node Stock ID'
            )->addColumn('cataloginventory_stock_id',
            	Ddl\Table::TYPE_SMALLINT,
            	5,
                ['unsigned' => true],
                'Catalog Stock ID'
            )->addColumn('qty',
            	Ddl\Table::TYPE_DECIMAL,
            	'12,4',
                ['nullable' => false],
                'Quantity'
            )->addColumn('stock_status',
            	Ddl\Table::TYPE_SMALLINT,
            	5,
                ['nullable' => false, 'unsigned' => true],
                'Stock Status'
            );
        $connection->createTable($stockZoneStatusTable);
    }
}
