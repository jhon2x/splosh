<?php

namespace Acidgreen\SploshBox\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

use Psr\Log\LoggerInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
	/**
	 * @var LoggerInterface
	 */
	protected $logger;
	
	public function __construct(
		LoggerInterface $logger
	) {
		$this->logger = $logger;
	}
	public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$setup->startSetup();
		
		$connection = $setup->getConnection();
		
		if (version_compare($context->getVersion(), '1.0.1') < 0) {
			$table = $setup->getTable('acidgreen_box');
			
            /*
			if (!$connection->tableColumnExists($table, 'website_id')) {
				$connection->addColumn(
					$table,
					'website_id',
					[
						'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
						'LENGTH' => 2,
						'NULLABLE' => true,
						'AFTER' => 'box_type',
						'COMMENT' => 'Website Scope ID'
					]
				);
				
				$this->logger->debug('Please check acidgreen_box table if there were DB structure changes.');
			}
             */
		}
		
		$setup->endSetup();
	}
}
