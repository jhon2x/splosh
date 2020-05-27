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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Setup\Upgrade;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema103 implements UpgradeSchemaInterface, VersionableInterface
{
    const VERSION = '1.0.3';

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * {@inheritDoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();

        $connection->addIndex(
            $setup->getTable($setup->getTable('mst_email_event')),
            $setup->getIdxName('mst_email_event', ['code']),
            ['code']
        );

        $connection->addIndex(
            $setup->getTable($setup->getTable('mst_email_event_trigger')),
            $setup->getIdxName('mst_email_event_trigger', ['event_id']),
            ['event_id']
        );
        $connection->addIndex(
            $setup->getTable($setup->getTable('mst_email_event_trigger')),
            $setup->getIdxName('mst_email_event_trigger', ['trigger_id']),
            ['trigger_id']
        );
        $connection->addIndex(
            $setup->getTable($setup->getTable('mst_email_event_trigger')),
            $setup->getIdxName('mst_email_event_trigger', ['status']),
            ['status']
        );
    }
}
