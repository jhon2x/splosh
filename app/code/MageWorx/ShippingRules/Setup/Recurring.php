<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\ShippingRules\Model\Rule;
use MageWorx\ShippingRules\Model\Zone;

/**
 * Catalog recurring setup
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $serializableTablesData;

    /**
     * UpgradeData constructor.
     *
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $serializableTablesData
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadata $productMetadata,
        \Psr\Log\LoggerInterface $logger,
        array $serializableTablesData = []
    ) {
        $this->productMetadata = $productMetadata;
        $this->logger          = $logger;
        /**
         * [
         *      ['table1' => ['col1', 'col2' ... ]
         *      ['table2' => ['col1', 'col2' ... ]
         * ]
         */
        $this->serializableTablesData = $serializableTablesData;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if ($this->isUsedJsonSerializedValues()) {
            try {
                $this->convertTableDataToJson($installer, Rule::TABLE_NAME, 'rule_id');
                $this->convertTableDataToJson($installer, Zone::ZONE_TABLE_NAME, 'entity_id');
            } catch (\Exception $exception) {
                $this->logger->debug('Exception:');
                $this->logger->debug($exception->getMessage());
                throw $exception;
            }
        }

        $installer->endSetup();
    }

    /**
     * @return bool
     */
    public function isUsedJsonSerializedValues()
    {
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.2.0', '>=') &&
            class_exists('\Magento\Framework\DB\AggregatedFieldDataConverter')
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param string $tableName
     * @param string $idColumnName
     * @return void
     */
    private function convertTableDataToJson(SchemaSetupInterface $setup, $tableName, $idColumnName)
    {
        if (empty($this->serializableTablesData[$tableName])) {
            return;
        }

        $connection          = $setup->getConnection();
        $tableNameReal       = $setup->getTable($tableName);
        $select              = $connection->select();
        $serializableColumns = $this->serializableTablesData[$tableName];
        $columns             = array_merge([$idColumnName], $serializableColumns);
        $select->from($tableNameReal, $columns);
        $data = $connection->fetchAll($select);
        foreach ($data as $datum) {
            foreach ($serializableColumns as $column) {
                if (!isset($datum[$column])) {
                    continue;
                }

                $data = json_decode($datum[$column]);
                if (json_last_error() === JSON_ERROR_NONE) {
                    continue;
                } else {
                    $data     = unserialize($datum[$column]);
                    $dataJson = json_encode($data);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        continue;
                    }
                    $connection->update(
                        $tableNameReal,
                        [
                            $column => $dataJson
                        ],
                        $connection->quoteInto($idColumnName . '=?', $datum[$idColumnName])
                    );
                }
            }
        }
    }
}
