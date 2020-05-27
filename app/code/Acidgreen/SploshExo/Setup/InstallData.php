<?php

namespace Acidgreen\SploshExo\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Psr\Log\LoggerInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{
    
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */

    private $eavSetupFactory;
	/**
	 * 
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;
	
	/**
	 * Class constructor
	 * @param LoggerInterface $logger
	 */
	public function __construct(
        EavSetupFactory $eavSetupFactory,
		LoggerInterface $logger
	) {
        $this->eavSetupFactory = $eavSetupFactory;
		$this->logger = $logger;	
	}
	
	/**
	 * Install data execution
	 * {@inheritDoc}
	 * @see \Magento\Framework\Setup\InstallDataInterface::install()
	 */
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$setup->startSetup();
		// create the ff. attributes here: exo_min_per_item, exo_ctn_size
        //
        $eavSetup = $this->eavSetupFactory->create(['setup'=>$setup]);
		
        $eavSetup->removeAttribute(
        	\Magento\Catalog\Model\Product::ENTITY,
            'exo_ctn_size');
		
        $eavSetup->removeAttribute(
        	\Magento\Catalog\Model\Product::ENTITY,
            'exo_mixed_box');

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'exo_ctn_size',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend_class' => '',
                'label' => 'Carton Size (EXO)',
                'input' => 'select',
                'class' => '',
                'source' => 'Acidgreen\SploshBox\Model\Config\Source\Box',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'apply_to' => '',
                'unique' => false
            ]
        );
/*
        // Add exo_mixed_box
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'exo_mixed_box',
            [
                'type' => 'int',
                'backend' => '',
                'frontend_class' => '',
                'label' => 'Mixed Box (EXO)',
                'input' => 'boolean',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'apply_to' => '',
                'unique' => false
            ]
        );
*/

        $this->logger->debug(__METHOD__.":: Please check if custom product attributes were added. Thank you.");
        // sleep(3);
		
		$setup->endSetup();
	}
}
