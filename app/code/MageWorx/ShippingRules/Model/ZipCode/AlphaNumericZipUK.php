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
 * Class AlphaNumericZipUK
 */
class AlphaNumericZipUK extends AbstractZip implements ZipCodeFormatInterface
{

    const TABLE_NAME  = 'mageworx_shippingrules_rates_zips_diapason_uk';
    const TABLE_ALIAS = 'zdtanuk';

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
            '`from` IS NULL OR (
                            `from_area` <= \'%1$s\' 
                            AND 
                            IF(`from_area` = \'%1$s\', `from_district_number` <= %2$d, 1)
                            AND 
                            IF(`from_area` = \'%1$s\' AND `from_district_number` = %2$d, (`from_district_letter` <= \'%3$s\' OR `from_district_letter` IS NULL), 1)
                            AND 
                            IF(`from_area` = \'%1$s\' AND `from_district_number` = %2$d AND (`from_district_letter` = \'%3$s\' OR `from_district_letter` IS NULL), (`from_sector` <= %4$d OR `from_sector` IS NULL), 1)
                            AND 
                            IF(`from_area` = \'%1$s\' AND `from_district_number` = %2$d AND (`from_district_letter` = \'%3$s\' OR `from_district_letter` IS NULL) AND (`from_sector` = %4$d OR `from_sector` IS NULL), (`from_unit` <= \'%5$s\' OR `from_unit` IS NULL), 1)
                        )',
            $zipParts['area'],
            $zipParts['district_number'],
            $zipParts['district_letter'],
            $zipParts['sector'],
            $zipParts['unit']
        );

        $toPartWhere = sprintf(
            '`to` IS NULL OR (
                            `to_area` >= \'%1$s\' 
                            AND 
                            IF(`to_area` = \'%1$s\', `to_district_number` >= %2$d, 1)
                            AND
                            IF(`to_area` = \'%1$s\' AND `to_district_number` = %2$d, (`to_district_letter` >= \'%3$s\' OR `to_district_letter` IS NULL), 1)
                            AND
                            IF(`to_area` = \'%1$s\' AND `to_district_number` = %2$d AND (`to_district_letter` = \'%3$s\' OR `to_district_letter` IS NULL), (`to_sector` >= %4$d OR `to_sector` IS NULL), 1)
                            AND
                            IF(`to_area` = \'%1$s\' AND `to_district_number` = %2$d AND (`to_district_letter` = \'%3$s\' OR `to_district_letter` IS NULL) AND (`to_sector` = %4$d OR `to_sector` IS NULL),(`to_unit` >= \'%5$s\' OR `to_unit` IS NULL), 1)
                        )',
            $zipParts['area'],
            $zipParts['district_number'],
            $zipParts['district_letter'],
            $zipParts['sector'],
            $zipParts['unit']
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
            ZipCodeManager::ALPHANUMERIC_FORMAT_UK,
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
        $zipParts     = $this->helper->parseUkPostCode($zip);
        $desiredParts = [
            'area'            => $zipParts['uk_area'],
            'district_number' => preg_replace('/[^\d]+/', '', $zipParts['uk_district']),
            'district_letter' => preg_replace('/[^A-Za-z]+/ui', '', $zipParts['uk_district']),
            'sector'          => $zipParts['uk_sector'],
            'unit'            => $zipParts['uk_unit']
        ];

        return $desiredParts;
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
            'Zip From As Is'
        )->addColumn(
            'to',
            Table::TYPE_TEXT,
            8,
            ['unsigned' => true, 'nullable' => true],
            'Zip To As Is'
        )->addColumn(
            'from_area',
            Table::TYPE_TEXT,
            2,
            ['nullable' => true],
            'From UK Zip Area'
        )->addColumn(
            'from_district_number',
            Table::TYPE_INTEGER,
            2,
            ['nullable' => true],
            'From UK Zip District (Numeric Part)'
        )->addColumn(
            'from_district_letter',
            Table::TYPE_TEXT,
            1,
            ['nullable' => true],
            'From UK Zip District (Letter Part)'
        )->addColumn(
            'from_sector',
            Table::TYPE_INTEGER,
            1,
            ['nullable' => true],
            'From UK Zip Sector'
        )->addColumn(
            'from_unit',
            Table::TYPE_TEXT,
            2,
            ['nullable' => true],
            'From UK Zip Unit'
        )->addColumn(
            'to_area',
            Table::TYPE_TEXT,
            2,
            ['nullable' => true],
            'To UK Zip Area'
        )->addColumn(
            'to_district_number',
            Table::TYPE_INTEGER,
            2,
            ['nullable' => true],
            'To UK Zip District (Numeric Part)'
        )->addColumn(
            'to_district_letter',
            Table::TYPE_TEXT,
            1,
            ['nullable' => true],
            'To UK Zip District (Letter Part)'
        )->addColumn(
            'to_sector',
            Table::TYPE_INTEGER,
            1,
            ['nullable' => true],
            'To UK Zip Sector'
        )->addColumn(
            'to_unit',
            Table::TYPE_TEXT,
            2,
            ['nullable' => true],
            'To UK Zip Unit'
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
            $setup->getIdxName(static::TABLE_NAME, ['from_area']),
            ['from_area'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['from_district_number']),
            ['from_district_number'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['from_district_letter']),
            ['from_district_letter'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['from_sector']),
            ['from_sector'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['from_unit']),
            ['from_unit'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['to_area']),
            ['to_area'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['to_district_number']),
            ['to_district_number'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['to_district_letter']),
            ['to_district_letter'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['to_sector']),
            ['to_sector'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(static::TABLE_NAME, ['to_unit']),
            ['to_unit'],
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
        return preg_match('/^([A-Za-z]{1,2}([\d]{1,2}[A-Za-z]{0,1}|[\d]{1}){1})[\s]?([\d]{1}[A-Za-z]{2})?$/', $zip);
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
            $fromParts = $this->getZipParts($datum['from']);
            $toParts   = $this->getZipParts($datum['to']);

            $datum['from_area']            = $fromParts['area'];
            $datum['from_district_number'] = $fromParts['district_number'];
            $datum['from_district_letter'] = $fromParts['district_letter'];
            $datum['from_sector']          = $fromParts['sector'];
            $datum['from_unit']            = $fromParts['unit'];

            $datum['to_area']            = $toParts['area'];
            $datum['to_district_number'] = $toParts['district_number'];
            $datum['to_district_letter'] = $toParts['district_letter'];
            $datum['to_sector']          = $toParts['sector'];
            $datum['to_unit']            = $toParts['unit'];
        }

        $dataChunks = array_chunk($data, 100);
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