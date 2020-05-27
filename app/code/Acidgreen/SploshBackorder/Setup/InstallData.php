<?php

namespace Acidgreen\SploshBackorder\Setup;


use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{

	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$setup->startSetup();
        $connection = $setup->getConnection();

        $stockZonesData = [
            [
                'stock_id' => '1', 
                'website_id' => '1', 
                'description' => 'Stock Zone - Australia B2C'
            ],
            [
                'stock_id' => '1', 
                'website_id' => '4', 
                'description' => 'Stock Zone - New Zealand B2C'
            ],
            [
                'stock_id' => '1', 
                'website_id' => '5', 
                'description' => 'Stock Zone - Australia B2B'
            ],
            [
                'stock_id' => '1', 
                'website_id' => '6', 
                'description' => 'Stock Zone - New Zealand B2B'
            ],
        ];

        $stockZoneTable = $setup->getTable('splosh_inventory_stockzone');

        foreach ($stockZonesData as $stockZoneData) {
            if ($connection->isTableExists($stockZoneTable)) {
                $connection->insert($stockZoneTable, $stockZoneData);
            }
        }

        $setup->endSetup();

	}
}
