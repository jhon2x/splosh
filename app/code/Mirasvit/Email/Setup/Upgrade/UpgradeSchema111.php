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

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;

class UpgradeSchema111 implements UpgradeSchemaInterface, VersionableInterface
{
    const VERSION = '1.1.1';

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

        $this->modifyTriggerTable($setup, $connection);
    }

    /**
     * Modify all trigger table.
     *
     * @param SchemaSetupInterface $setup
     * @param AdapterInterface     $connection
     */
    private function modifyTriggerTable(SchemaSetupInterface $setup, AdapterInterface $connection)
    {
        $connection->addForeignKey(
            $setup->getFkName(
                TriggerInterface::TABLE_NAME,
                CampaignInterface::ID,
                CampaignInterface::TABLE_NAME,
                CampaignInterface::ID
            ),
            $setup->getTable(TriggerInterface::TABLE_NAME),
            CampaignInterface::ID,
            $setup->getTable(CampaignInterface::TABLE_NAME),
            CampaignInterface::ID,
            Table::ACTION_CASCADE
        );
    }
}
