<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\ZipCode;

use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\ShippingRules\Api\ZipCodeFormatInterface;
use MageWorx\ShippingRules\Model\ResourceModel\Rate\Collection as RatesCollection;
use MageWorx\ShippingRules\Api\Data\RateInterface;
use MageWorx\ShippingRules\Model\Carrier;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use MageWorx\ShippingRules\Model\ZipCodeManager;

/**
 * Class AlphaNumericCanadianZip
 */
class AlphaNumericZip extends AbstractZip implements ZipCodeFormatInterface
{
    const TABLE_NAME  = 'mageworx_shippingrules_rates_zips_diapason_alphanumeric';
    const TABLE_ALIAS = 'zdtan';

    /**
     * Filters rates collection by current format of zip code
     *
     * @param RatesCollection $collection
     * @param string|int $destinationZip
     * @return mixed
     */
    public function createConditionByZip(RatesCollection $collection, $destinationZip)
    {
        $excludedZipDiapasonSelect = $collection->getConnection()->select()->from(
            $collection->getTable(static::TABLE_NAME),
            ['rate_id']
        )->where(
            '`from` <= ? OR `from` IS NULL',
            $destinationZip
        )->where(
            '`to` >= ? OR `to` IS NULL',
            $destinationZip
        )->where(
            'inverted = ?',
            1
        );

        $condition = sprintf(
            '`main_table`.`zip_validation_mode` = 2
                          AND
                          `main_table`.`zip_format` = \'%2$s\'
                          AND
                          `main_table`.`rate_id` NOT IN (%3$s)
                          AND
                          (
                                `%1$s`.`inverted` IS NULL
                                OR
                                (
                                    (`from` <= ? OR `from` IS NULL)
                                    AND
                                    (`to` >= ? OR `to` IS NULL)
                                    AND
                                    `%1$s`.`inverted` = 0
                                )
                          )',
            static::TABLE_ALIAS,
            ZipCodeManager::ALPHANUMERIC_FORMAT,
            $excludedZipDiapasonSelect
        );

        return $condition;
    }

    /**
     * Creates own table (with indexes and foreign keys) where zip diapasons saved in desired format
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function createDbSchema(SchemaSetupInterface $setup)
    {
        $ratesTable     = $setup->getTable(Carrier::RATE_TABLE_NAME);
        $refIdFieldName = RateInterface::ENTITY_ID_FIELD_NAME;

        $table = $setup->getConnection()->newTable(
            $setup->getTable(static::TABLE_NAME)
        )->addColumn(
            'rate_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Rate Id'
        )->addColumn(
            'from',
            Table::TYPE_TEXT,
            12,
            ['nullable' => true],
            'Zip From'
        )->addColumn(
            'to',
            Table::TYPE_TEXT,
            12,
            ['nullable' => true],
            'Zip To'
        )->addColumn(
            'inverted',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => false],
            'Is diapason inverted (Not In)'
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['rate_id', 'from', 'to']),
            ['rate_id', 'from', 'to'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['inverted']),
            ['inverted'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['from']),
            ['from'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['to']),
            ['to'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $setup->getFkName(
                static::TABLE_NAME,
                'rate_id',
                Carrier::RATE_TABLE_NAME,
                $refIdFieldName
            ),
            'rate_id',
            $ratesTable,
            $refIdFieldName,
            Table::ACTION_CASCADE
        )->setComment(
            'MageWorx Shipping Rules Zips From-To/Rate Relations Table for (Numeric format)'
        );

        $setup->getConnection()->createTable($table);
    }

    /**
     * Check is format suitable for zip-code
     *
     * @param string|int $zip
     * @return boolean
     */
    public function isSuitableZip($zip)
    {
        return preg_match('/[^\d+]/', $zip);
    }

    /**
     * Insert data
     *
     * @param array $data
     * @return int
     */
    public function bulkInsertUpdate(array $data)
    {
        $dataChunks = array_chunk($data, 500);
        $table      = $this->resourceConnection->getTableName($this->getTableName());
        $result     = 0;

        foreach ($dataChunks as $chunk) {
            $result += $this->resourceConnection->getConnection()->insertOnDuplicate(
                $table,
                $chunk
            );
        }

        return $result;
    }

    /**
     * Returns name of the table where zip code diapasons stored in that format
     *
     * @return string
     */
    public function getTableName()
    {
        return static::TABLE_NAME;
    }
}