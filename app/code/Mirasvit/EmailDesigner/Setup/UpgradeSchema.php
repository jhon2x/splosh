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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Mirasvit\EmailDesigner\Api\Data\ThemeInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var File
     */
    private $file;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        File $file
    ) {
        $this->file = $file;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->upgrade1_0_1($setup, $context);
        $this->upgrade1_0_2($setup, $context);

        $setup->endSetup();
    }

    /**
     * Upgrade date columns for extension's tables.
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    private function upgrade1_0_1(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $connection = $setup->getConnection();

            foreach ([TemplateInterface::CREATED_AT, TemplateInterface::UPDATED_AT] as $columnName) {
                $connection->modifyColumn(
                    $setup->getTable(TemplateInterface::TABLE_NAME),
                    $columnName,
                    [
                        'type'     => Table::TYPE_TIMESTAMP,
                        'nullable' => false,
                        'default'  => $columnName == TemplateInterface::CREATED_AT
                            ? Table::TIMESTAMP_INIT
                            : Table::TIMESTAMP_INIT_UPDATE
                    ]
                );
            }
        }
    }

    /**
     * Create system_id column for templates.
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    private function upgrade1_0_2(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $connection = $setup->getConnection();

            $connection->addColumn(
                $setup->getTable($setup->getTable(TemplateInterface::TABLE_NAME)),
                TemplateInterface::SYSTEM_ID,
                [
                    'type'     => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'unsigned' => true,
                    'comment'  => TemplateInterface::SYSTEM_ID,
                    'after'    => TemplateInterface::DESCRIPTION
                ]
            );
        }
    }
}
