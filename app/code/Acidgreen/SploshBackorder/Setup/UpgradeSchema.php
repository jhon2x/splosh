<?php

namespace Acidgreen\SploshBackorder\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$setup->startSetup();
		
		$connection = $setup->getConnection();
		
        // Add "partner" b2c website id for a stockzone here...
		// if (version_compare($context->getVersion(), '1.0.1') < 0) {
		// }
		
		$setup->endSetup();
	}
}
