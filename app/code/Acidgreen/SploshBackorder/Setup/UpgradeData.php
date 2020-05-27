<?php

namespace Acidgreen\SploshBackorder\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl;

 
class UpgradeData implements UpgradeDataInterface 
{
    
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function upgrade( ModuleDataSetupInterface $setup, ModuleContextInterface $context ) {
        
        $installer = $setup;

        if(!$context->getVersion()) {
            //no previous version found, installation, InstallSchema was just executed
        }

        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            
            //$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'exo_product_id');

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'force_backorder',
                [
                    'type' => 'int',
                    'source' => '\Acidgreen\SploshBackorder\Model\Product\Attribute\Source\Backorders',
                    'label' => 'Force Backorder',
                    'scope' => 'website',
                    'input' => 'text',
                    'class' => '',
            		'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible' => true,
                    'required' => false,
                    'default_value' => '0',
                    'is_user_defined' => '1',
                    'is_visible' => true,
                    'is_filterable' => true,
                    'is_visible_on_front' => true,
                    'is_searchable' => true,
                    'searchable' => true,
                ]
            );

        }
    }
}
