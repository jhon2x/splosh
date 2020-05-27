<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Setup;

use Amasty\Rules\Api\Data\RuleInterface;
use Magento\Framework\App\State;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ExternalFKSetup;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class UpgradeSchema
 * @codingStandardsIgnoreFile
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var Operation\AddAmrulesTable
     */
    private $addAmrulesTable;

    /**
     * @var Operation\MigrateRules
     */
    private $migrateRules;

    /**
     * @var ExternalFKSetup
     */
    private $externalFKSetup;

    /**
     * @var MetadataPool
     */
    private $metadata;

    public function __construct(
        State $appState,
        Operation\AddAmrulesTable $addAmrulesTable,
        Operation\MigrateRules $migrateRules,
        ExternalFKSetup $externalFKSetup,
        MetadataPool $metadata
    ) {
        $this->appState = $appState;
        $this->addAmrulesTable = $addAmrulesTable;
        $this->migrateRules = $migrateRules;
        $this->externalFKSetup = $externalFKSetup;
        $this->metadata = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addAmrulesTable->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->appState->emulateAreaCode(
                \Magento\Framework\App\Area::AREA_FRONTEND,
                [$this->migrateRules, 'execute']
            );
        }

        /** @since 2.5.1 deleted deprecated code of changing foreign key for EE (2.1.1) */

        if (version_compare($context->getVersion(), '2.2.3', '<')) {
            $this->addApplyDiscountTo($setup);
        }

        if (version_compare($context->getVersion(), '2.2.4', '<')) {
            $this->addUseFor($setup);
        }

        if (version_compare($context->getVersion(), '2.5.1', '<')) {
            $this->changeForeignKey($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addApplyDiscountTo(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amrules_rule'),
            'apply_discount_to',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 4,
                'nullable' => false,
                'comment'  => 'Apply Discount To'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addUseFor(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amrules_rule'),
            'use_for',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length'   => 4,
                'nullable' => false,
                'comment'  => 'Use'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Exception
     */
    private function changeForeignKey(SchemaSetupInterface $setup)
    {
        /** @var AdapterInterface $adapter */
        $adapter = $setup->getConnection();
        $amruleTableName = $setup->getTable('amasty_amrules_rule');
        $salesruleTableName = $setup->getTable('salesrule');
        $foreignKeys = $adapter->getForeignKeys($amruleTableName);
        $linkField = $this->metadata->getMetadata(\Magento\SalesRule\Api\Data\RuleInterface::class)->getLinkField();

        foreach ($foreignKeys as $key) {
            if ($key['COLUMN_NAME'] == RuleInterface::KEY_SALESRULE_ID && $key['REF_COLUMN_NAME'] != $linkField) {
                $this->setRowIdInsteadRuleId($adapter, $amruleTableName, $salesruleTableName);
                $adapter->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
                $this->externalFKSetup->install(
                    $setup,
                    $salesruleTableName,
                    $this->metadata->getMetadata(\Magento\SalesRule\Api\Data\RuleInterface::class)->getLinkField(),
                    $amruleTableName,
                    RuleInterface::KEY_SALESRULE_ID
                );
            }
        }
    }

    /**
     * @param AdapterInterface $adapter
     * @param string $amruleTableName
     * @param string $salesruleTableName
     *
     * @throws \Zend_Db_Select_Exception
     */
    private function setRowIdInsteadRuleId($adapter, $amruleTableName, $salesruleTableName)
    {
        $select = $adapter->select()
            ->from(
                $amruleTableName,
                [
                    'eachm',
                    'priceselector',
                    'promo_cats',
                    'promo_skus',
                    'nqty',
                    'skip_rule',
                    'max_discount',
                    'apply_discount_to',
                    'use_for'
                ]
            )->joinInner(
                ['salesrule' => $salesruleTableName],
                'salesrule.rule_id = ' . $amruleTableName . '.salesrule_id',
                ['salesrule_id' => 'salesrule.row_id']
            )->setPart('disable_staging_preview', true);

        $amRules = $adapter->fetchAll($select);

        $adapter->truncateTable($amruleTableName);

        foreach ($amRules as $rule) {
            $adapter->insert($amruleTableName, $rule);
        }
    }
}
