<?php

namespace Acidgreen\CustomerRestrictions\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
	private $eavSetupFactory;

	/**
	 * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
	 */
	public function __construct(
		EavSetupFactory $eavSetupFactory
	) {
		$this->eavSetupFactory = $eavSetupFactory;
	}
	
	public function upgrade(
		ModuleDataSetupInterface $setup, 
		ModuleContextInterface $context
	) {
		$setup->startSetup();
		
		if (version_compare($context->getVersion(), '1.0.1') < 0) {
    		//set default of range_restrictions to null
    		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

	        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Customer\Model\Customer::ENTITY);
	        $attribute = $eavSetup->getAttribute($entityTypeId, 'range_restrictions');

	        if ($attribute) {
	            $eavSetup->updateAttribute($entityTypeId, $attribute['attribute_id'], 'default_value', null);
	        }
    	}
	}
}
