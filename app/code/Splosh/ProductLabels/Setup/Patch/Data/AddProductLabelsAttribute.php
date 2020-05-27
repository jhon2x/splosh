<?php

namespace Splosh\ProductLabels\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use \Psr\Log\LoggerInterface;

/**
 * Class AddProductLabelsAttribute
 * @package Splosh\ProductLabels\Setup\Patch\Data
 */
class AddProductLabelsAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AddProductLabelsAttribute constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        LoggerInterface $logger
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        try {

            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

            $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'product_label');

            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'product_label', [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Product Label',
                'input' => 'select',
                'class' => '',
                'source' => \Splosh\ProductLabels\Model\Source\ProductLabels::class,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]);

        } catch (\Exception $e) {
            $this->logger->error($e->__toString());
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }


    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}