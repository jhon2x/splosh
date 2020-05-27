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
 * @package   mirasvit/module-misspell
 * @version   1.0.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Misspell\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class Recurring implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('mst_misspell_index');
        $indexName = $installer->getIdxName(
            'mst_misspell_index',
            'trigram',
            AdapterInterface::INDEX_TYPE_FULLTEXT
        );

        $indices = $installer->getConnection()->getIndexList($tableName);

        if (!key_exists($indexName, $indices)
            || $indices[$indexName]['type'] != AdapterInterface::INDEX_TYPE_FULLTEXT) {
            $installer->getConnection()->dropIndex($tableName, $indexName);

            $installer->getConnection()->addIndex(
                $tableName,
                $indexName,
                'trigram',
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }

        $installer->endSetup();
    }
}
