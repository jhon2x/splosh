<?php


namespace Acidgreen\SploshBox\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $table_acidgreen_box = $setup->getConnection()->newTable($setup->getTable('acidgreen_box'));

        
        $table_acidgreen_box->addColumn(
            'box_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            array('identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,),
            'Entity ID'
        );
        

        
        $table_acidgreen_box->addColumn(
            'box_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Carton Size'
        );
        

        
        $table_acidgreen_box->addColumn(
            'multi_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'Multi. Qty'
        );
        
        $table_acidgreen_box->addColumn('is_active',
        	\Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
        	null,
        	['default' => true],
        	'Is Active'
        );

        $setup->getConnection()->createTable($table_acidgreen_box);

        $setup->endSetup();
    }
}
