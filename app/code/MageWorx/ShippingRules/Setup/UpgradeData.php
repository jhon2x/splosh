<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Setup;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Setup\Exception;
use MageWorx\ShippingRules\Api\Data\RateInterface;
use MageWorx\ShippingRules\Model\Region as RegionModel;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\EntityManager\MetadataPool;
use MageWorx\ShippingRules\Api\Data\RuleInterface;
use MageWorx\ShippingRules\Model\Rule;
use MageWorx\ShippingRules\Api\Data\ZoneInterface;
use MageWorx\ShippingRules\Model\ZipCodeManager;
use MageWorx\ShippingRules\Model\Zone;
use MageWorx\ShippingRules\Model\Carrier;
use Psr\Log\LoggerInterface;

/**
 * Class UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{
    const MAX_ITERATIONS_COUNT = 1000;

    /**
     * @var ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\DB\AggregatedFieldDataConverter
     */
    private $aggregatedFieldConverter;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ZipCodeManager
     */
    private $zipCodeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * UpgradeData constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ObjectManagerInterface $objectManager
     * @param ProductMetadata $productMetadata
     * @param EavSetupFactory $eavSetupFactory
     * @param ZipCodeManager $zipCodeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        MetadataPool $metadataPool,
        ObjectManagerInterface $objectManager,
        ProductMetadata $productMetadata,
        EavSetupFactory $eavSetupFactory,
        ZipCodeManager $zipCodeManager,
        LoggerInterface $logger
    ) {
        $this->productMetadata = $productMetadata;
        $this->metadataPool    = $metadataPool;
        if ($this->isUsedJsonSerializedValues()) {
            $this->aggregatedFieldConverter = $objectManager->get('Magento\Framework\DB\AggregatedFieldDataConverter');
            $this->jsonSerializer           = $objectManager->get('Magento\Framework\Serialize\Serializer\Json');
        }
        $this->eavSetupFactory = $eavSetupFactory;
        $this->zipCodeManager  = $zipCodeManager;
        $this->logger          = $logger;
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
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup = $setup;

        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.6.0', '<')) {
            $this->addDefaultValuesForDefaultRegions($setup);
        }

        if (version_compare($context->getVersion(), '2.0.2', '<') && $this->aggregatedFieldConverter) {
            $this->convertRuleSerializedDataToJson($setup);
            $this->convertZoneSerializedDataToJson($setup);
        }

        if (version_compare($context->getVersion(), '2.0.7', '<')) {
            $this->createRatesCodesFromIds($setup);
        }

        if (version_compare($context->getVersion(), '2.4.0', '<')) {
            $this->addShippingPerProductAttributes();
        }

        if (version_compare($context->getVersion(), '2.6.0', '<')) {
            $this->logger->debug('Transfer data start:');
            try {
                // Transfer countries
                $this->transferCountriesFromRateToIndexTable($setup);

                // Transfer regions and region ids
                $this->transferRegionsFromRateToIndexTable($setup);
                $this->transferRegionIdsFromRateToIndexTable($setup);

                // Transfer Zip Codes (plain & diapasons)
                $this->transferZipsFromRateToIndexTables($setup);

                // Create dump of the rates table
                $this->createRatesTableDump($setup);

                // Remove abandoned data
                $this->dropUnnecessaryRatesConditionsColumns($setup);
                $this->removeCarrierIdColumnFromMethodsTable($setup);
                $this->removeMethodIdColumnFromRatesTable($setup);
            } catch (\Exception $e) {
                $this->logger->debug('Exception: ' . $e->getMessage());
                $this->logger->debug($e->getTraceAsString());

                throw $e;
            }
            $this->logger->debug('Transfer data ends;');
        }

        $setup->endSetup();
    }

    /**
     * Add default values for the default regions:
     * is_active = 1
     * is_custom = 0
     *
     * @param ModuleDataSetupInterface $setup
     */
    protected function addDefaultValuesForDefaultRegions(ModuleDataSetupInterface $setup)
    {
        $connection           = $setup->getConnection();
        $regionsTable         = $setup->getTable('directory_country_region');
        $extendedRegionsTable = $setup->getTable(RegionModel::EXTENDED_REGIONS_TABLE_NAME);
        $select               = $connection->select()->from($regionsTable, ['region_id']);
        $query                = $connection->insertFromSelect(
            $select,
            $extendedRegionsTable,
            ['region_id'],
            AdapterInterface::INSERT_IGNORE
        );
        $connection->query($query);
        $connection->update($extendedRegionsTable, ['is_active' => 1, 'is_custom' => 0]);
    }

    /**
     * Convert Rule metadata from serialized to JSON format:
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     * @throws \Magento\Framework\DB\FieldDataConversionException
     */
    protected function convertRuleSerializedDataToJson(ModuleDataSetupInterface $setup)
    {
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $this->aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable(Rule::TABLE_NAME),
                    $metadata->getLinkField(),
                    'conditions_serialized'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable(Rule::TABLE_NAME),
                    $metadata->getLinkField(),
                    'actions_serialized'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable(Rule::TABLE_NAME),
                    $metadata->getLinkField(),
                    'amount'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable(Rule::TABLE_NAME),
                    $metadata->getLinkField(),
                    'action_type'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable(Rule::TABLE_NAME),
                    $metadata->getLinkField(),
                    'shipping_methods'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable(Rule::TABLE_NAME),
                    $metadata->getLinkField(),
                    'disabled_shipping_methods'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable(Rule::TABLE_NAME),
                    $metadata->getLinkField(),
                    'enabled_shipping_methods'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable(Rule::TABLE_NAME),
                    $metadata->getLinkField(),
                    'store_errmsgs'
                ),
            ],
            $setup->getConnection()
        );
    }

    /**
     * Convert Zone metadata from serialized to JSON format:
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     * @throws \Magento\Framework\DB\FieldDataConversionException
     */
    protected function convertZoneSerializedDataToJson(ModuleDataSetupInterface $setup)
    {
        $metadata = $this->metadataPool->getMetadata(ZoneInterface::class);
        $this->aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable(Zone::ZONE_TABLE_NAME),
                    $metadata->getLinkField(),
                    'conditions_serialized'
                ),
            ],
            $setup->getConnection()
        );
    }

    /**
     * Add default codes for the existing rates from its id with prefix
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function createRatesCodesFromIds(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $ratesTable = $setup->getTable(Carrier::RATE_TABLE_NAME);
        $connection->update(
            $ratesTable,
            ['rate_code' => new \Zend_Db_Expr("CONCAT('rate_',`rate_id`)")],
            ["`rate_code` IS NULL OR `rate_code` = ''"]
        );
    }

    /**
     * Adds available_shipping_methods attribute to the product EAV-model
     */
    private function addShippingPerProductAttributes()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();

        $availableShippingMethodsAttribute = $eavSetup->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'available_shipping_methods'
        );
        if (empty($availableShippingMethodsAttribute)) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'available_shipping_methods',
                [
                    'group'                    => 'General',
                    'type'                     => 'text',
                    'label'                    => 'Available Shipping Methods',
                    'input'                    => 'multiselect',
                    'required'                 => false,
                    'sort_order'               => 40,
                    'global'                   => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid'          => true,
                    'is_visible_in_grid'       => true,
                    'is_filterable_in_grid'    => true,
                    'visible'                  => true,
                    'is_html_allowed_on_front' => false,
                    'visible_on_front'         => false,
                    'system'                   => 0,
                    // should be 0 to access this attribute everywhere
                    'user_defined'             => false,
                    // should be false to prevent deleting from admin-side interface
                    'source'                   =>
                        \MageWorx\ShippingRules\Model\Attribute\Source\AvailableShippingMethods::class,
                    'frontend'                 =>
                        \MageWorx\ShippingRules\Model\Attribute\Frontend\AvailableShippingMethods::class,
                    'backend'                  =>
                        \MageWorx\ShippingRules\Model\Attribute\Backend\AvailableShippingMethods::class,
                    // Extends Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend
                ]
            );
        }
    }

    /**
     * Transfer countries data by rate id from old column to the new index table
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function transferCountriesFromRateToIndexTable(ModuleDataSetupInterface $setup)
    {
        $connection             = $setup->getConnection();
        $ratesTable             = $setup->getTable(Carrier::RATE_TABLE_NAME);
        $ratesCountriesIdxTable = $setup->getTable(RateInterface::RATE_COUNTRY_TABLE_NAME);

        if (!$connection->tableColumnExists($ratesTable, 'country_id')) {
            return;
        }

        $select  = $connection->select()->from($ratesTable, [RateInterface::ENTITY_ID_FIELD_NAME, 'country_id'])
                              ->where("country_id > ''");
        $rawData = $connection->fetchAll($select);
        if (empty($rawData)) {
            return;
        }

        $data = [];
        foreach ($rawData as $datum) {
            $rateId    = $datum[RateInterface::ENTITY_ID_FIELD_NAME];
            $countries = explode(',', $datum['country_id']);
            foreach ($countries as $country) {
                $data[] = [
                    'rate_id'      => $rateId,
                    'country_code' => $country
                ];
            }
        }

        $connection->insertOnDuplicate($ratesCountriesIdxTable, $data);
    }

    /**
     * Transfer regions data by rate id from old column to the new index table
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function transferRegionsFromRateToIndexTable(ModuleDataSetupInterface $setup)
    {
        $connection           = $setup->getConnection();
        $ratesTable           = $setup->getTable(Carrier::RATE_TABLE_NAME);
        $ratesRegionsIdxTable = $setup->getTable(RateInterface::RATE_REGION_TABLE_NAME);

        if (!$connection->tableColumnExists($ratesTable, 'region')) {
            return;
        }

        $select  = $connection->select()->from($ratesTable, [RateInterface::ENTITY_ID_FIELD_NAME, 'region'])
                              ->where("region > ''");
        $rawData = $connection->fetchAll($select);
        if (empty($rawData)) {
            return;
        }

        $data = [];
        foreach ($rawData as $datum) {
            $rateId  = $datum[RateInterface::ENTITY_ID_FIELD_NAME];
            $regions = explode(',', $datum['region']);
            foreach ($regions as $region) {
                $data[] = [
                    'rate_id' => $rateId,
                    'region'  => $region
                ];
            }
        }

        $connection->insertOnDuplicate($ratesRegionsIdxTable, $data);
    }

    /**
     * Transfer region ids data by rate id from old column to the new index table
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function transferRegionIdsFromRateToIndexTable(ModuleDataSetupInterface $setup)
    {
        $connection             = $setup->getConnection();
        $ratesTable             = $setup->getTable(Carrier::RATE_TABLE_NAME);
        $ratesRegionIdsIdxTable = $setup->getTable(RateInterface::RATE_REGION_ID_TABLE_NAME);

        if (!$connection->tableColumnExists($ratesTable, 'region_id')) {
            return;
        }

        $select  = $connection->select()->from($ratesTable, [RateInterface::ENTITY_ID_FIELD_NAME, 'region_id'])
                              ->where("region_id > ''");
        $rawData = $connection->fetchAll($select);
        if (empty($rawData)) {
            return;
        }

        $data = [];
        foreach ($rawData as $datum) {
            $rateId    = $datum[RateInterface::ENTITY_ID_FIELD_NAME];
            $regionIds = explode(',', $datum['region_id']);
            foreach ($regionIds as $regionId) {
                $data[] = [
                    'rate_id'   => $rateId,
                    'region_id' => $regionId
                ];
            }
        }

        $connection->insertOnDuplicate($ratesRegionIdsIdxTable, $data);
    }

    /**
     * Get old records (zip-codes rules) from the rates table and transfer it to own specific tables
     *
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    private function transferZipsFromRateToIndexTables(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $ratesTable = $setup->getTable(Carrier::RATE_TABLE_NAME);

        if (!$connection->tableColumnExists($ratesTable, 'zip_from')) {
            return;
        }

        if (!$connection->tableColumnExists($ratesTable, 'zip_to')) {
            return;
        }

        $page = 0;
        do {
            $select  = $connection->select()->from(
                $ratesTable,
                [RateInterface::ENTITY_ID_FIELD_NAME, 'zip_from', 'zip_to']
            )
                                  ->where("zip_from IS NOT NULL")
                                  ->limitPage($page, 100);
            $rawData = $connection->fetchAll($select);
            if (empty($rawData)) {
                return;
            }

            $this->logger->debug(sprintf('Page: %d', $page));

            $this->doTransferZips($rawData);
            $page++;
        } while (!empty($rawData) && $page < 10000);
    }

    /**
     * Transfer zip method
     *
     * @param array $rawData
     * @throws LocalizedException
     */
    private function doTransferZips($rawData)
    {
        $dataPlainZips                 = [];
        $dataDiapasonZips              = [];
        $plainZipValidationRateIds     = [];
        $diapasonZipsValidationRateIds = [];
        $diapasonZipsFormats           = [];

        foreach ($rawData as $datum) {
            $rateId  = $datum[RateInterface::ENTITY_ID_FIELD_NAME];
            $zipFrom = $datum['zip_from'];
            $zipTo   = $datum['zip_to'];

            // Parse zip from
            if (empty($zipFrom) && $zipFrom !== '0') {
                continue;
            } else {
                $zipFromArray = explode(',', $zipFrom);
            }

            // Parse zip to
            if (empty($zipTo)) {
                $isDiapason = false;
            } else {
                $isDiapason = true;
                $zipToArray = explode(',', $zipTo);
            }

            if ($isDiapason) {
                // Working with diapason
                $count = min(count($zipFromArray), count($zipToArray));
                $i     = 0;
                while ($i < $count) {
                    $zipFromData = $zipFromArray[$i];
                    $zipToData   = $zipToArray[$i];

                    $zipFromDataWithoutSpaces = trim($zipFromData);
                    if (stripos($zipFromDataWithoutSpaces, '!') === 0) {
                        $inverted     = true;
                        $cleanZipFrom = str_ireplace('!', '', $zipFromDataWithoutSpaces);
                    } else {
                        $inverted     = false;
                        $cleanZipFrom = $zipFromDataWithoutSpaces;
                    }

                    $cleanZipTo = str_ireplace('!', '', trim($zipToData));

                    if ($cleanZipFrom != $cleanZipTo) {
                        $formatFrom = $this->zipCodeManager->detectFormat($cleanZipFrom);
                        $formatTo   = $this->zipCodeManager->detectFormat($cleanZipTo);
                        if ($formatFrom === $formatTo) {
                            $dataDiapasonZips[$formatFrom][]    = [
                                'rate_id'  => $rateId,
                                'from'     => $cleanZipFrom,
                                'to'       => $cleanZipTo,
                                'inverted' => $inverted
                            ];
                            $diapasonZipsValidationRateIds[]    = $rateId;
                            $diapasonZipsFormats[$formatFrom][] = $rateId;
                        }
                    } else {
                        // Correcting in case zip from equals zip to
                        $dataPlainZips[]             = [
                            'rate_id'  => $rateId,
                            'zip'      => $cleanZipFrom,
                            'inverted' => $inverted
                        ];
                        $plainZipValidationRateIds[] = $rateId;
                    }

                    $i++;
                }
            } else {
                // Working with plain zip codes
                foreach ($zipFromArray as $zipFromData) {
                    $zipFromDataWithoutSpaces = trim($zipFromData);
                    if (stripos($zipFromDataWithoutSpaces, '!') === 0) {
                        $inverted = true;
                        $cleanZip = str_ireplace('!', '', $zipFromDataWithoutSpaces);
                    } else {
                        $inverted = false;
                        $cleanZip = $zipFromDataWithoutSpaces;
                    }

                    $dataPlainZips[] = [
                        'rate_id'  => $rateId,
                        'zip'      => $cleanZip,
                        'inverted' => $inverted
                    ];

                    $plainZipValidationRateIds[] = $rateId;
                }
            }
        }

        // Insert data
        $this->insertPlainZips($dataPlainZips);
        $this->insertDiapasonsZip($dataDiapasonZips);
        $this->insertZipValidationModeForRates(
            $plainZipValidationRateIds,
            RateInterface::ZIP_VALIDATION_MODE_PLAIN
        );
        $this->insertZipValidationModeForRates(
            $diapasonZipsValidationRateIds,
            RateInterface::ZIP_VALIDATION_MODE_DIAPASON
        );
        $this->insertDiapasonsZipFormatForRates($diapasonZipsFormats);

        // Log Result
        $allRecordsCount = count($rawData);
        $diapasonsCount  = 0;
        foreach ($dataDiapasonZips as $dataDiapasonZip) {
            $diapasonsCount += count($dataDiapasonZip);
        }
        $plainZipsCount          = count($dataPlainZips);
        $unprocessedRecordsCount = $allRecordsCount - ($diapasonsCount + $plainZipsCount);

        $this->logger->debug(sprintf('Overall Records: %d', $allRecordsCount));
        $this->logger->debug(sprintf('Diapasons: %d', $diapasonsCount));
        $this->logger->debug(sprintf('Plain Zips: %d', $plainZipsCount));
        $this->logger->debug(sprintf('Not Processed: %d', $unprocessedRecordsCount));
    }

    /**
     * Insert zip_format values in the rates table
     *
     * @param array $diapasonZipsFormats
     */
    private function insertDiapasonsZipFormatForRates(array $diapasonZipsFormats)
    {
        if (empty($diapasonZipsFormats)) {
            return;
        }

        $connection = $this->setup->getConnection();
        $ratesTable = $this->setup->getTable(Carrier::RATE_TABLE_NAME);

        foreach ($diapasonZipsFormats as $format => $zipsForFormat) {
            if (!empty($zipsForFormat)) {
                $zipsForFormat = array_unique($zipsForFormat);
                $whereAsString = 'rate_id IN (' . implode(',', $zipsForFormat) . ')';
                $connection->update(
                    $ratesTable,
                    [
                        'zip_format' => $format
                    ],
                    $whereAsString
                );
            }
        }
    }

    /**
     * Insert zip_validation_mode values in the rates table
     *
     * @param array $rateIds
     * @param int $mode
     */
    private function insertZipValidationModeForRates(array $rateIds, $mode)
    {
        if (empty($rateIds)) {
            return;
        }

        $connection = $this->setup->getConnection();
        $ratesTable = $this->setup->getTable(Carrier::RATE_TABLE_NAME);

        $rateIds       = array_unique($rateIds);
        $whereAsString = 'rate_id IN (' . implode(',', $rateIds) . ')';

        $connection->update(
            $ratesTable,
            [
                'zip_validation_mode' => $mode
            ],
            $whereAsString
        );
    }

    /**
     * Insert zip diapasons
     *
     * @param array $dataDiapasonZips
     * @throws LocalizedException
     */
    private function insertDiapasonsZip(array $dataDiapasonZips)
    {
        // Insert diapasons according its format
        foreach ($dataDiapasonZips as $format => $formatZipsData) {
            if (empty($formatZipsData)) {
                continue;
            }

            try {
                $formatter = $this->zipCodeManager->getFormatter($format);
                $formatter->bulkInsertUpdate($formatZipsData);
            } catch (LocalizedException $exception) {
                $this->logger->debug($exception->getMessage());

                throw $exception;
            }
        }
    }

    /**
     * Insert plain zips
     *
     * @param array $dataPlainZips
     */
    private function insertPlainZips(array $dataPlainZips)
    {
        if (empty($dataPlainZips)) {
            return;
        }

        $plainZipsTable = $this->setup->getTable(RateInterface::RATE_ZIPS_TABLE_NAME);
        $connection     = $this->setup->getConnection();

        $dataPlainZipsChunks = array_chunk($dataPlainZips, 256);
        foreach ($dataPlainZipsChunks as $chunk) {
            $connection->insertOnDuplicate($plainZipsTable, $chunk);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws Exception
     */
    private function createRatesTableDump(ModuleDataSetupInterface $setup)
    {
        $connection     = $setup->getConnection();
        $ratesTable     = $setup->getTable(Carrier::RATE_TABLE_NAME);
        $ratesTableDump = $setup->getTable(Carrier::RATE_TABLE_NAME . '_dump');
        $i              = 1;
        while ($connection->isTableExists($ratesTableDump)) {
            $ratesTableDump = $setup->getTable(sprintf(Carrier::RATE_TABLE_NAME . '_dump_%d', $i));
            $i++;
            if ($i >= self::MAX_ITERATIONS_COUNT) {
                throw new Exception('Something goes wrong during creation dump of the rates table.');
            }
        }
        $sql = sprintf('CREATE TABLE %1$s AS SELECT * FROM %2$s;', $ratesTableDump, $ratesTable);
        $connection->query($sql);
    }

    /**
     * Drop old columns from the rates table
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function dropUnnecessaryRatesConditionsColumns(ModuleDataSetupInterface $setup)
    {
        $connection         = $setup->getConnection();
        $ratesTable         = $setup->getTable(Carrier::RATE_TABLE_NAME);
        $unnecessaryColumns = [
            'region',
            'region_id',
            'country_id',
            'zip_from',
            'zip_to'
        ];

        foreach ($unnecessaryColumns as $column) {
            $connection->dropColumn($ratesTable, $column);
        }
    }

    /**
     * Remove the `carrier_id` column from the methods table (preferred way: using `carrier_code` column instead)
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function removeCarrierIdColumnFromMethodsTable(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $table      = $setup->getTable(Carrier::METHOD_TABLE_NAME);
        $connection->dropColumn($table, 'carrier_id');
    }

    /**
     * Remove the `method_id` column from the rates table (preferred way: using `method_code` column instead)
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function removeMethodIdColumnFromRatesTable(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $table      = $setup->getTable(Carrier::RATE_TABLE_NAME);
        $connection->dropColumn($table, 'method_id');
    }
}
