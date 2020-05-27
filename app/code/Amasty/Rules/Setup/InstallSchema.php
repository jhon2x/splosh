<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class InstallSchema
 * @codingStandardsIgnoreFile
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var MetadataPool
     */
    private $metadata;

    public function __construct(
        MetadataPool $metadata
    ) {
        $this->metadata = $metadata;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $salesruleLinkField = $this->metadata->getMetadata(\Magento\SalesRule\Api\Data\RuleInterface::class)
            ->getLinkField();

        /**
         * Create table 'amasty_amrules_rule'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_amrules_rule'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'salesrule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Salesrule Entity Id'
            )
            ->addColumn(
                'eachm',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Each M Product'
            )
            ->addColumn(
                'priceselector',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Price Base On'
            )
            ->addColumn(
                'promo_cats',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Additional Y cats'
            )
            ->addColumn(
                'promo_skus',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Additional Y skus'
            )
            ->addColumn(
                'nqty',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'N Qty'
            )
            ->addColumn(
                'skip_rule',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Skip Rule'
            )
            ->addColumn(
                'max_discount',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Max Discount Amount'
            )

            ->addIndex(
                $installer->getIdxName('amasty_amrules_rule', ['salesrule_id']),
                ['salesrule_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_amrules_rule',
                    'salesrule_id',
                    'salesrule',
                    $salesruleLinkField
                ),
                'salesrule_id',
                $installer->getTable('salesrule'),
                $salesruleLinkField,
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Amasty Promotions Rules Table');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
