<?php

namespace Acidgreen\SploshBox\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Psr\Log\LoggerInterface;

class InstallData implements InstallDataInterface
{
	protected $logger;
	
	public function __construct(
		LoggerInterface $logger
	) {
		$this->logger = $logger;	
	}
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$setup->startSetup();
		
		$tableName = $setup->getTable('acidgreen_box');
		
		if ($setup->getConnection()->isTableExists($tableName))
		{
			$data = [
				[
					'box_type' => 'MED. BOX',
					'multi_qty' => '4',
					'is_active' => true
				],
				[
					'box_type' => 'MED. LEAF SHADE',
					'multi_qty' => '3',
					'is_active' => true
				],
				[
					'box_type' => 'MEMORY JAR BOX',
					'multi_qty' => '6',
					'is_active' => true
				],
			];
			
			foreach ($data as $item) {
				$setup->getConnection()->insert($tableName, $item);
			}
			
			$this->logger->debug(__METHOD__.' :: Please see if data was entered to '.$tableName);
		}
		
		$setup->endSetup();
	}
}