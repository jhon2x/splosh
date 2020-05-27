<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    public function __construct(
        IndexRepositoryInterface $indexRepository
    ) {
        $this->indexRepository = $indexRepository;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->getConnection()->dropTable($installer->getTable('mst_search_index'));

        /**
         * Create table 'mst_search_index'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_search_index')
        )->addColumn(
            'index_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Index Id'
        )->addColumn(
            'identifier',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Index Code'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Title'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false, 'default' => '0'],
            'Position'
        )->addColumn(
            'attributes_serialized',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Attributes'
        )->addColumn(
            'properties_serialized',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Properties'
        )->addColumn(
            'status',
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            'Status'
        )->addColumn(
            'is_active',
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            'Is Active'
        )->setComment(
            'Search Index'
        );

        $installer->getConnection()->createTable($table);

        // create default index
        $productIndex = $this->indexRepository->create()
            ->setIdentifier('catalogsearch_fulltext')
            ->setTitle('Products')
            ->setIsActive(1)
            ->setPosition(1);
        $this->indexRepository->save($productIndex);
    }
}
