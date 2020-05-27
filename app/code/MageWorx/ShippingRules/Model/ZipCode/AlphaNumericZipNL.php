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
 * Class AlphaNumericZipNL
 */
class AlphaNumericZipNL extends AbstractZip implements ZipCodeFormatInterface
{
    const TABLE_NAME  = 'mageworx_shippingrules_rates_zips_diapason_nl';
    const TABLE_ALIAS = 'zdtannl';

    /**
     * Filters rates collection by current format of zip code
     *
     * @param RatesCollection $collection
     * @param string|int $destinationZip
     * @return mixed
     */
    public function createConditionByZip(RatesCollection $collection, $destinationZip)
    {
        $zipParts = $this->getZipParts($destinationZip);

        $fromPartWhere = sprintf(
            '`from` IS NULL 
                    OR 
                    (
                        `from_digit_part` <= %1$d 
                        AND 
                        IF(`from_digit_part` = %1$d, `from_letter_part` <= \'%2$s\', 1)
                    )',
            $zipParts['digit_part'],
            $zipParts['letter_part']
        );

        $toPartWhere = sprintf(
            '`to` IS NULL 
                    OR 
                    (
                        `to_digit_part` >= %1$d 
                        AND 
                        IF(`to_digit_part` = %1$d, (`to_letter_part` >= \'%2$s\' OR `to_letter_part` IS NULL), 1)
                    )',
            $zipParts['digit_part'],
            $zipParts['letter_part']
        );

        $excludedZipDiapasonSelect = $collection->getConnection()->select()->from(
            $collection->getTable(static::TABLE_NAME),
            ['rate_id']
        )->where(
            $fromPartWhere
        )->where(
            $toPartWhere
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
                                    (%4$s)
                                    AND
                                    (%5$s)
                                    AND
                                    `%1$s`.`inverted` = 0
                                )
                          )',
            static::TABLE_ALIAS,
            ZipCodeManager::ALPHANUMERIC_FORMAT_NL,
            $excludedZipDiapasonSelect,
            $fromPartWhere,
            $toPartWhere
        );

        return $condition;
    }

    /**
     * Get parts of zip code
     * - numeric (like 1000)
     * - letter (like AB)
     *
     * @param string $zip
     * @return array
     */
    public function getZipParts($zip)
    {
        $clearZip = preg_replace('/[^\dA-Za-z]+/', '', $zip);

        preg_match('/^([\d]{4})([A-Za-z]{2})$/ui', $clearZip, $matches);

        $parts = [
            'digit_part'  => isset($matches[1]) ? (int)$matches[1] : 1,
            'letter_part' => isset($matches[2]) ? (string)$matches[2] : ''
        ];

        return $parts;
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

        $tableFromToZips = $setup->getConnection()->newTable(
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
            8,
            ['unsigned' => true, 'nullable' => true],
            'Zip From Link'
        )->addColumn(
            'to',
            Table::TYPE_TEXT,
            8,
            ['unsigned' => true, 'nullable' => true],
            'Zip To Link'
        )->addColumn(
            'from_digit_part',
            Table::TYPE_INTEGER,
            4,
            ['nullable' => true],
            'From Digit Part Of Zip'
        )->addColumn(
            'from_letter_part',
            Table::TYPE_TEXT,
            2,
            ['nullable' => true],
            'From Letter Part Of Zip'
        )->addColumn(
            'to_digit_part',
            Table::TYPE_INTEGER,
            4,
            ['nullable' => true],
            'To Digit Part Of Zip'
        )->addColumn(
            'to_letter_part',
            Table::TYPE_TEXT,
            2,
            ['nullable' => true],
            'To Letter Part Of Zip'
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
            $setup->getIdxName(static::TABLE_NAME, ['from_digit_part']),
            ['from_digit_part'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['from_letter_part']),
            ['from_letter_part'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['to_digit_part']),
            ['to_digit_part'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['to_letter_part']),
            ['to_letter_part'],
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

        $setup->getConnection()->createTable($tableFromToZips);
    }

    /**
     * Check is format suitable for zip-code
     *
     * @param string|int $zip
     * @return boolean
     */
    public function isSuitableZip($zip)
    {
        return preg_match('/^[\d]{4}\s?[A-Za-z]{2}$/', $zip);
    }

    /**
     * Insert data
     *
     * @param array $data
     * @return int
     */
    public function bulkInsertUpdate(array $data)
    {
        $table = $this->resourceConnection->getTableName($this->getTableName());
        foreach ($data as $key => &$datum) {
            // Parsing
            $fromParts = $this->getZipParts($datum['from']);
            $toParts   = $this->getZipParts($datum['to']);

            // Save Digits
            $datum['from_digit_part'] = $fromParts['digit_part'];
            $datum['to_digit_part']   = $toParts['digit_part'];

            // Save Letters
            $datum['from_letter_part'] = $fromParts['letter_part'];
            $datum['to_letter_part']   = $toParts['letter_part'];
        }

        $dataChunks = array_chunk($data, 500);
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