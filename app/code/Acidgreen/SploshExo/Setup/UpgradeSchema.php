<?php

namespace Acidgreen\SploshExo\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB;
use Psr\Log\LoggerInterface;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
	/**
	 * 
	 * @var LoggerInterface
	 */
	protected $logger;
	
	public function __construct(
		LoggerInterface $logger
	) {
		$this->logger = $logger;
	}
    public function upgrade(
    	SchemaSetupInterface $setup, 
    	ModuleContextInterface $context)
    {
    	$setup->startSetup();
    	
    	$connection = $setup->getConnection();
    	if (version_compare($context->getVersion(), '0.0.2') < 0) {
    		$boxTable = $setup->getTable('acidgreen_box');
    		
    		if (!$connection->tableColumnExists($boxTable, 'is_mixed_box')) {
    			$connection->addColumn($boxTable, 'is_mixed_box', [
    				'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    				'LENGTH' => 1,
    				'NULLABLE' => true,
    				'AFTER' => 'box_type',
    				'COMMENT' => 'Mixed Box'
    			]);
    		}
    	}
    	if (version_compare($context->getVersion(), '0.0.5') < 0) {
    		/**
    		 * splosh_staff table:
    		 * id
    		 * exo_staff_id
    		 * name
    		 * jobtitle
    		 */
    		$staffTable = $setup->getTable('splosh_staff');
    		
    		$table = $setup->getConnection()->newTable($staffTable);
    		$table
    			->addColumn(
    				'id', 
    				DB\Ddl\Table::TYPE_INTEGER,
    				null,
    				[
    					'identity'=> true,
    					'unsigned' => true,
    					'nullable' => false,
    					'primary' => true
    				],
    				'ID'
    			)->addColumn(
    				'exo_staff_id',
    				DB\Ddl\Table::TYPE_INTEGER,
    				null,
    				['nullable' => true],
    				'EXO Staff ID'
    			)->addColumn(
    				'name',
    				DB\Ddl\Table::TYPE_TEXT,
    				255,
    				['nullable' => false],
    				'EXO Staff Name'
    			)->addColumn(
    				'nickname',
    				DB\Ddl\Table::TYPE_TEXT,
    				64,
    				['nullable' => false],
    				'Nickname'
    			)->addColumn(
    				'jobtitle',
    				DB\Ddl\Table::TYPE_TEXT,
    				255,
    				['nullable' => true],
    				'Job Title'
    			)->addColumn(
    				'email',
    				DB\Ddl\Table::TYPE_TEXT,
    				255,
    				['nullable' => true],
    				'Email'
    			)->addColumn(
    				'phone_no',
    				DB\Ddl\Table::TYPE_TEXT,
    				32,
    				['nullable' => true],
    				'Phone number'
				)->addColumn(
                    'website_id',
                    DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'Website ID'
				)->addColumn(
                    'is_active',
                    DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => true],
                    'Is Active'
                )->setComment('Table for EXO Staff table');
    		
    			$setup->getConnection()->createTable($table);
    			
    		$this->logger->info(__METHOD__.' :: Please verify if table splosh_staff was created. Thank you.');
    		
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            /**
             * splosh_customer_group:
             * id
             * mage_group_id
             * exo_group_id
             * description
             */
            $sploshCustomerTable = $setup->getConnection()->newTable($setup->getTable('splosh_customer_group'));

            $sploshCustomerTable
                ->addColumn('id',
                    DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'Entity ID'
                )->addColumn('mage_group_id',
                    DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Magento Customer Group ID'
                )->addColumn('exo_group_id',
                    DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'EXO Customer Group ID'
                )->addColumn('description',
                    DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Description'
                );

            $connection->createTable($sploshCustomerTable);
        }
    	
    	$setup->endSetup();
    }
}
