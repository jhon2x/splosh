<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\ImportExport;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\ShippingRules\Api\CarrierRepositoryInterface;
use MageWorx\ShippingRules\Api\Data\RateInterface;
use MageWorx\ShippingRules\Api\ImportHandlerInterface;
use MageWorx\ShippingRules\Api\MethodRepositoryInterface;
use MageWorx\ShippingRules\Api\RateRepositoryInterface;
use MageWorx\ShippingRules\Model\Carrier as CarrierModel;
use MageWorx\ShippingRules\Model\Carrier\Method as MethodModel;
use MageWorx\ShippingRules\Model\Carrier\Method\Rate as RateModel;
use MageWorx\ShippingRules\Model\ZipCodeManager;
use MageWorx\ShippingRules\Model\ResourceModel\Carrier\CollectionFactory as CarrierCollectionFactory;
use MageWorx\ShippingRules\Model\ResourceModel\Method\CollectionFactory as MethodCollectionFactory;
use MageWorx\ShippingRules\Model\ResourceModel\Rate\CollectionFactory as RateCollectionFactory;
use MageWorx\ShippingRules\Helper\Data as Helper;
use Magento\Framework\File\Csv as CSVProcessor;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class ExpressImportHandler
 */
class ExpressImportHandler extends AbstractImportExport implements ImportHandlerInterface
{
    /**
     * @var CarrierCollectionFactory
     */
    private $carrierCollectionFactory;

    /**
     * @var MethodCollectionFactory
     */
    private $methodCollectionFactory;

    /**
     * @var RateCollectionFactory
     */
    private $rateCollectionFactory;

    /**
     * @var ZipCodeManager
     */
    private $zipCodeManager;

    /**
     * @var array
     */
    private $availableStoreIds = [];

    /**
     * ExpressImportHandler constructor.
     *
     * @param MethodsMap $reflectionMethodsMap
     * @param CarrierRepositoryInterface $carrierRepository
     * @param MethodRepositoryInterface $methodRepository
     * @param RateRepositoryInterface $rateRepository
     * @param Helper $helper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataObject\Factory $dataObjectFactory
     * @param StoreManagerInterface $storeManager
     * @param CSVProcessor $csvProcessor
     * @param ObjectManagerInterface $objectManager
     * @param MessageManager $messageManager
     * @param EventManager $eventManager
     * @param CarrierCollectionFactory $carrierCollectionFactory
     * @param MethodCollectionFactory $methodCollectionFactory
     * @param RateCollectionFactory $rateCollectionFactory
     * @param ZipCodeManager $zipCodeManager
     * @param array $entities
     * @param array $data
     */
    public function __construct(
        MethodsMap $reflectionMethodsMap,
        CarrierRepositoryInterface $carrierRepository,
        MethodRepositoryInterface $methodRepository,
        RateRepositoryInterface $rateRepository,
        Helper $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObject\Factory $dataObjectFactory,
        StoreManagerInterface $storeManager,
        CSVProcessor $csvProcessor,
        ObjectManagerInterface $objectManager,
        MessageManager $messageManager,
        EventManager $eventManager,
        CarrierCollectionFactory $carrierCollectionFactory,
        MethodCollectionFactory $methodCollectionFactory,
        RateCollectionFactory $rateCollectionFactory,
        ZipCodeManager $zipCodeManager,
        array $entities = [],
        array $data = []
    ) {
        parent::__construct(
            $reflectionMethodsMap,
            $carrierRepository,
            $methodRepository,
            $rateRepository,
            $helper,
            $searchCriteriaBuilder,
            $dataObjectFactory,
            $storeManager,
            $csvProcessor,
            $objectManager,
            $messageManager,
            $eventManager,
            $entities,
            $data
        );
        $this->carrierCollectionFactory = $carrierCollectionFactory;
        $this->methodCollectionFactory  = $methodCollectionFactory;
        $this->rateCollectionFactory    = $rateCollectionFactory;
        $this->zipCodeManager           = $zipCodeManager;
    }

    /**
     * Import Carriers, Methods, Rates from CSV file
     *
     * @param array $file file info retrieved from $_FILES array
     * @param array $entities
     * @return void
     * @throws LocalizedException
     * @throws \Exception
     */
    public function importFromCsvFile($file, $entities = [])
    {
        if (!isset($file['tmp_name'])) {
            throw new LocalizedException(__('Invalid file upload attempt.'));
        }

        $data           = $this->csvProcessor->getData($file['tmp_name']);
        $headersAsArray = $this->parseHeaders($data[0]);
        $keys           = array_values($headersAsArray);
        $typeIndex      = array_search(static::ENTITY_TYPE, $keys);

        if ($typeIndex === false) {
            throw new LocalizedException(__('Invalid file upload attempt: Unable to find type column index.'));
        }

        $carriers = [];
        $methods  = [];
        $rates    = [];

        foreach ($data as $dataIndex => $values) {
            // Recognize object entity type: is it a carrier, method or rate
            if (empty($values[$typeIndex])) {
                continue;
            }

            switch ($values[$typeIndex]) {
                case self::RATE_ENTITY_KEY:
                    $rates[] = $values;
                    break;
                case self::METHOD_ENTITY_KEY:
                    $methods[] = $values;
                    break;
                case self::CARRIER_ENTITY_KEY:
                    $carriers[] = $values;
                    break;
                default:
                    continue;
            }
        }

        unset($data);

        if (!empty($carriers)) {
            $this->importCarriers($carriers, $keys);
            unset($carriers);
        }

        if (!empty($methods)) {
            $this->importMethods($methods, $keys);
            unset($methods);
        }

        if (!empty($rates) && count($rates) < 1000) {
            $this->importRates($rates, $keys);
        } else {
            $ratesChunked = array_chunk($rates, 1000);
            foreach ($ratesChunked as $key => $ratesChunk) {
                $this->importRates($ratesChunk, $keys);
                unset($ratesChunked[$key]);
            }
        }
    }

    /**
     * Parse headers from the first line of the CSV file
     *
     * @param array $firstRow
     * @return array
     */
    private function parseHeaders($firstRow)
    {
        $headers = [];
        foreach ($firstRow as $column) {
            $headers[] = implode('_', explode(' ', trim(mb_strtolower($column))));
        }

        return $headers;
    }

    /**
     * @param $entityType
     * @param array $ignoredColumnsBase
     * @return array
     */
    private function detectAllowedColumns($entityType, array $ignoredColumnsBase = [])
    {
        $allowedColumns = $this->getDataFields([$entityType]);

        foreach ($ignoredColumnsBase as $ignoredColumn) {
            if (isset($allowedColumns[$ignoredColumn])) {
                unset($allowedColumns[$ignoredColumn]);
            }
        }

        return $allowedColumns;
    }

    /**
     * @param array $map
     * @param string $column
     * @return false|int|string
     */
    private function getIndex(array &$map, $column)
    {
        return array_search($column, $map);
    }

    /**
     * @param array $carriers
     * @param array $keys
     * @return mixed
     * @throws \Zend_Db_Exception
     */
    private function importCarriers(&$carriers, array &$keys)
    {
        // Detect ignored columns
        $ignoredColumns = [
                'ie_type',
                'id'
            ] + CarrierModel::getIgnoredColumnsForImportExport();

        $allowedColumns = $this->detectAllowedColumns(static::CARRIER_ENTITY_KEY, $ignoredColumns);

        $availableStoreIds = $this->getAvailableStoreIds();
        $filteredCarriers  = [];

        foreach ($allowedColumns as $allowedColumn => $allowedColumnTitle) {
            foreach ($carriers as $key => $carrier) {
                $allowedColumnIndex = $this->getIndex($keys, $allowedColumn);
                $codeColumnIndex    = $this->getIndex($keys, 'carrier_code');
                if ($allowedColumnIndex === false || $codeColumnIndex === false) {
                    continue;
                }

                if (isset($carrier[$allowedColumnIndex])) {
                    if ($allowedColumn == 'store_ids') {
                        $storeIds = explode(',', $carrier[$allowedColumnIndex]);

                        foreach ($storeIds as $storeId) {
                            if (in_array($storeId, $availableStoreIds)) {
                                $storesData[] = [
                                    'store_id'     => $storeId,
                                    'carrier_code' => $carrier[$codeColumnIndex]
                                ];
                            }
                        }
                    } else {
                        $filteredCarriers[$key][$allowedColumn] = $carrier[$allowedColumnIndex];
                    }
                }
            }
        }

        $carrierCollection = $this->carrierCollectionFactory->create();
        $tableName         = $carrierCollection->getTable(CarrierModel::CARRIER_TABLE_NAME);
        $conn              = $carrierCollection->getConnection();

        $data        = $filteredCarriers;
        $qtyInserted = $this->insertData($data, $conn, $tableName);

        if (!empty($storesData)) {
            $codeField       = 'carrier_code';
            $idField         = 'carrier_id';
            $storesTableName = CarrierModel::CARRIER_TABLE_NAME . '_store';
            $this->updateStoresForEntity(
                $storesData,
                $this->carrierCollectionFactory,
                $codeField,
                $idField,
                $storesTableName
            );
        }

        $this->eventManager->dispatch(
            'mageworx_shippingrules_import_insert_carriers',
            [
                'data'       => $filteredCarriers,
                'collection' => $carrierCollection
            ]
        );

        return $qtyInserted;
    }

    /**
     * Returns array with all available store ids
     *
     * @return array
     */
    protected function getAvailableStoreIds()
    {
        if (empty($this->availableStoreIds)) {
            $stores = $this->storeManager->getStores(true);
            $ids    = [];
            foreach ($stores as $store) {
                $ids[] = $store->getId();
            }

            $this->availableStoreIds = $ids;
        }

        return $this->availableStoreIds;
    }

    /**
     * @param array $data
     * @param \Magento\Framework\Db\Adapter\AdapterInterface|\Magento\Framework\DB\Adapter\Pdo\Mysql $conn
     * @param string $tableName
     * @return mixed
     * @throws \Zend_Db_Exception
     */
    public function insertData(&$data, $conn, $tableName)
    {
        if (empty($data)) {
            return 0;
        }

        $row         = reset($data);
        $cols        = array_keys($row);
        $insertArray = [];
        foreach ($data as $row) {
            $line = [];
            if (array_diff($cols, array_keys($row))) {
                throw new \Zend_Db_Exception('Invalid data for insert');
            }
            foreach ($cols as $field) {
                $line[] = $row[$field];
            }
            $insertArray[] = $line;
        }
        unset($row);

        try {
            if (count($data) < 1000) {
                $qtyInserted = $conn->insertOnDuplicate($tableName, $data, $cols);
            } else {
                $parts       = array_chunk($data, 1000);
                $qtyInserted = 0;
                foreach ($parts as $part) {
                    $qtyInserted += $conn->insertOnDuplicate($tableName, $part, $cols);
                }
            }
        } catch (\Zend_Db_Statement_Exception $exception) {
            $this->messageManager->addErrorMessage(
                __('Something goes wrong with an importing file. Please, check it\'s format and retry.')
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        return $qtyInserted;
    }

    /**
     * Insert\Update store ids data related to entities
     * Input array should have 2 field:
     * [
     *     $codeField => string,
     *     'store_id' => int|string
     * ]
     *
     * @param array $storesData
     * @param CarrierCollectionFactory|MethodCollectionFactory|RateCollectionFactory $collectionFactory
     * @param string $codeField
     * @param string $idField
     * @param string $tableName
     * @return int
     * @throws \Zend_Db_Exception
     */
    private function updateStoresForEntity(
        array &$storesData,
        $collectionFactory,
        $codeField,
        $idField,
        $tableName
    ) {
        $parts       = array_chunk($storesData, 1000);
        $qtyInserted = 0;

        foreach ($parts as $part) {
            $codes = [];
            foreach ($part as $storeData) {
                $codes[] = $storeData[$codeField];
            }
            $codesUnique = array_unique($codes);

            $collection = $collectionFactory->create();
            $collection->addFieldToFilter($codeField, ['in' => $codesUnique]);
            $collection->addFieldToSelect([$codeField, $idField]);
            $select = $collection->getSelect();
            $conn   = $collection->getConnection();
            $data   = $conn->fetchAll($select);

            foreach ($part as $key => &$storeData) {
                $code = $storeData[$codeField];
                $id   = null;
                foreach ($data as $datum) {
                    if ($datum[$codeField] == $code) {
                        $id = $datum[$idField];
                        break;
                    }
                }

                if (empty($id)) {
                    unset($part[$key]);
                    continue;
                } else {
                    unset($storeData[$codeField]);
                    $storeData['entity_id'] = $id; // Id field in the store table always named as `entity_id`
                }
            }

            $table       = $collection->getTable($tableName);
            $data        = $part;
            $qtyInserted += $this->insertData($data, $conn, $table);
        }

        return $qtyInserted;
    }

    /**
     * @param array $methods
     * @param array $keys
     * @return mixed
     * @throws \Zend_Db_Exception
     */
    private function importMethods(&$methods, array &$keys)
    {
        // Detect ignored columns
        $ignoredColumns = [
                'ie_type',
                'id'
            ] + MethodModel::getIgnoredColumnsForImportExport();

        $allowedColumns = $this->detectAllowedColumns(static::METHOD_ENTITY_KEY, $ignoredColumns);

        $availableStoreIds = $this->getAvailableStoreIds();
        $filteredMethods   = [];

        foreach ($allowedColumns as $allowedColumn => $allowedColumnTitle) {
            foreach ($methods as $key => $method) {
                $allowedColumnIndex = $this->getIndex($keys, $allowedColumn);
                $codeColumnIndex    = $this->getIndex($keys, 'code');
                if ($allowedColumnIndex === false || $codeColumnIndex === false) {
                    continue;
                }

                if (isset($method[$allowedColumnIndex])) {
                    if ($allowedColumn == 'store_ids') {
                        $storeIds = explode(',', $method[$allowedColumnIndex]);

                        foreach ($storeIds as $storeId) {
                            if (in_array($storeId, $availableStoreIds)) {
                                $storesData[] = [
                                    'store_id' => $storeId,
                                    'code'     => $method[$codeColumnIndex]
                                ];
                            }
                        }
                    } else {
                        $filteredMethods[$key][$allowedColumn] = $method[$allowedColumnIndex];
                    }
                }
            }
        }

        $methodCollection = $this->methodCollectionFactory->create();
        $tableName        = $methodCollection->getTable(CarrierModel::METHOD_TABLE_NAME);
        $conn             = $methodCollection->getConnection();

        $data        = $filteredMethods;
        $qtyInserted = $this->insertData($data, $conn, $tableName);

        if (!empty($storesData)) {
            $codeField       = 'code';
            $idField         = 'entity_id';
            $storesTableName = CarrierModel::METHOD_TABLE_NAME . '_store';
            $this->updateStoresForEntity(
                $storesData,
                $this->methodCollectionFactory,
                $codeField,
                $idField,
                $storesTableName
            );
        }

        $this->eventManager->dispatch(
            'mageworx_shippingrules_import_insert_methods',
            [
                'data'       => $filteredMethods,
                'collection' => $methodCollection
            ]
        );

        return $qtyInserted;
    }

    /**
     * @param array $rates
     * @param array $keys
     * @return mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Exception
     */
    private function importRates(&$rates, array &$keys)
    {
        // Detect ignored columns
        $ignoredColumns = [
                'ie_type',
                'id'
            ] + RateModel::getIgnoredColumnsForImportExport();;

        $allowedColumns = $this->detectAllowedColumns(static::RATE_ENTITY_KEY, $ignoredColumns);

        $availableStoreIds = $this->getAvailableStoreIds();
        $filteredRates     = [];
        $storesData        = [];

        $allowedColumnsIdxs = [];
        foreach ($allowedColumns as $allowedColumnKey => $allowedColumn) {
            $allowedColumnIdx                      = $this->getIndex($keys, $allowedColumnKey);
            $allowedColumnsIdxs[$allowedColumnIdx] = $allowedColumnKey;
        }

        foreach ($allowedColumns as $allowedColumn => $allowedColumnTitle) {
            foreach ($rates as $key => $rate) {
                $allowedColumnIndex = $this->getIndex($keys, $allowedColumn);
                $codeColumnIndex    = $this->getIndex($keys, 'rate_code');
                if ($allowedColumnIndex === false || $codeColumnIndex === false) {
                    continue;
                }

                $zipFormatColumnIdx       = $this->getIndex($keys, 'zip_format');
                $zipCodeDiapasonColumnIdx = $this->getIndex($keys, 'zip_code_diapasons');
                if ($zipFormatColumnIdx === false || $zipCodeDiapasonColumnIdx === false) {
                    continue;
                }

                if (empty($rate[$zipFormatColumnIdx]) && !empty($rate[$zipCodeDiapasonColumnIdx])) {
                    $rate[$zipFormatColumnIdx] = $this->detectZipDiapasonsFormat($rate);
                }

                if (isset($rate[$allowedColumnIndex])) {
                    if ($allowedColumn == 'store_ids') {
                        if ($rate[$allowedColumnIndex] === '') {
                            continue;
                        }

                        $storeIds = explode(',', $rate[$allowedColumnIndex]);
                        foreach ($storeIds as $storeId) {
                            if (in_array($storeId, $availableStoreIds)) {
                                $storesData[] = [
                                    'store_id'  => $storeId,
                                    'rate_code' => $rate[$codeColumnIndex]
                                ];
                            }
                        }
                    } else {
                        $filteredRates[$key][$allowedColumn] = $rate[$allowedColumnIndex];
                    }
                }
            }
        }

        $ratesCollection = $this->rateCollectionFactory->create();
        $tableName       = $ratesCollection->getTable(CarrierModel::RATE_TABLE_NAME);
        $conn            = $ratesCollection->getConnection();

        $data        = $this->filterRatesDataBeforeInsert($filteredRates);
        $qtyInserted = $this->insertData($data, $conn, $tableName);

        $this->insertRatesStores($storesData);
        $this->insertRatesCountries($filteredRates);
        $this->insertRatesRegionIds($filteredRates);
        $this->insertRatesRegions($filteredRates);
        $this->insertRatesPlainZipCodes($filteredRates);
        $this->insertRatesZipCodeDiapasons($filteredRates);

        $this->eventManager->dispatch(
            'mageworx_shippingrules_import_insert_rates',
            [
                'data'       => $filteredRates,
                'collection' => $ratesCollection
            ]
        );

        return $qtyInserted;
    }

    /**
     * Detect zip diapsons format using first element in zip codes diapason array
     *
     * @param array $rateData
     * @return string
     */
    private function detectZipDiapasonsFormat($rateData)
    {
        if (empty($rateData['zip_code_diapasons'])) {
            return ZipCodeManager::NUMERIC_FORMAT;
        }

        $diapasons     = explode(',', $rateData['zip_code_diapasons']);
        $firstDiapason = $diapasons[0];

        $cleanDiapason = trim($firstDiapason);
        $cleanDiapason = str_ireplace('!', '', $cleanDiapason);
        $diapasonArray = explode('-', $cleanDiapason);
        if (empty($diapasonArray[0])) {
            return ZipCodeManager::NUMERIC_FORMAT;
        }

        $from      = $diapasonArray[0];
        $cleanFrom = trim($from);

        $format = $this->zipCodeManager->detectFormat($cleanFrom);

        return $format;
    }

    /**
     * Remove redundant columns from rates data
     *
     * @param array $data
     * @return array
     */
    private function filterRatesDataBeforeInsert(array &$data)
    {
        $ratesData = $data;
        foreach ($ratesData as $key => &$rateData) {
            unset($rateData['country_id']);
            unset($rateData['region_id']);
            unset($rateData['region']);
            unset($rateData['plain_zip_codes']);
            unset($rateData['zip_code_diapasons']);
        }

        /**
         * @important Don't simplify it!
         *
         * Since it is a strange PHP interpreter issue - so when the value is transferred to the event,
         * it has no changes apart of the last array element.
         */
        $dataTransferObject = $this->dataObjectFactory->create(['rates_data' => &$ratesData]);
        $this->eventManager->dispatch(
            'mageworx_filter_rates_data_before_insert',
            ['data_transfer_object' => $dataTransferObject]
        );
        $updatedRatesData = $dataTransferObject->getData('rates_data');

        return $updatedRatesData;
    }

    /**
     * @param array $storesData
     * @throws \Zend_Db_Exception
     */
    private function insertRatesStores(array $storesData = [])
    {
        if (empty($storesData)) {
            return;
        }

        $codeField       = 'rate_code';
        $idField         = 'rate_id';
        $storesTableName = CarrierModel::RATE_TABLE_NAME . '_store';
        $this->updateStoresForEntity(
            $storesData,
            $this->rateCollectionFactory,
            $codeField,
            $idField,
            $storesTableName
        );
    }

    /**
     * @param array $filteredRates
     * @throws \Zend_Db_Exception
     */
    private function insertRatesCountries(array $filteredRates = [])
    {
        if (empty($filteredRates)) {
            return;
        }

        $ratesCollection = $this->rateCollectionFactory->create();
        $conn            = $ratesCollection->getConnection();

        // Country
        $ratesWithFilledField = [];
        $field                = 'country_id';
        foreach ($filteredRates as $rate) {
            if (!empty($rate[$field])) {
                $ratesWithFilledField[] = $rate;
            }
        }

        if (!empty($ratesWithFilledField)) {
            $rateCountryRelationData = $this->getRateRelationData(
                $ratesWithFilledField,
                'country_id',
                'country_code',
                'rate_id',
                'rate_id'
            );
            $this->insertData(
                $rateCountryRelationData,
                $conn,
                $ratesCollection->getTable(RateInterface::RATE_COUNTRY_TABLE_NAME)
            );
        }
    }

    /**
     * @param array $data
     * @param string $fieldName
     * @param string $refFieldName
     * @param string $keyFieldName
     * @param string $refKeyFieldName
     * @return array
     */
    private function getRateRelationData(array $data, $fieldName, $refFieldName, $keyFieldName, $refKeyFieldName)
    {
        if (empty($data)) {
            return [];
        }

        $data = $this->fillRatesWithRateId($data);

        $relationsData = [];
        $errors        = 0;
        foreach ($data as $datum) {
            if (empty($datum[$keyFieldName])) {
                $errors++;
                continue;
            }
            $values = explode(',', $datum[$fieldName]);
            foreach ($values as $value) {
                $relationsData[] = [
                    $refKeyFieldName => $datum[$keyFieldName],
                    $refFieldName    => $value
                ];
            }
        }

        return $relationsData;
    }

    /**
     * Add rate id field to the data array
     *
     * @param array $data
     * @return array
     */
    public function fillRatesWithRateId(array $data = [])
    {
        if (empty($data)) {
            return $data;
        }

        $parts = array_chunk($data, 1000, true);

        foreach ($parts as $partKey => &$part) {
            $dataCodes = [];
            foreach ($part as $partData) {
                $dataCodes[] = $partData['rate_code'];
            }

            $dataCodes = array_unique($dataCodes);

            /** @var \MageWorx\ShippingRules\Model\ResourceModel\Rate\Collection $ratesCollection */
            $ratesCollection = $this->rateCollectionFactory->create();
            $ratesCollection->addFieldToSelect(['rate_id', 'rate_code']);
            $ratesCollection->addFieldToFilter('rate_code', ['in' => $dataCodes]);
            $select    = $ratesCollection->getSelect();
            $conn      = $ratesCollection->getConnection();
            $ratesData = $conn->fetchAll($select);

            reset($part);
            foreach ($part as $key => &$datum) {
                $rateId = null;
                foreach ($ratesData as $rateData) {
                    if ($rateData['rate_code'] == $datum['rate_code']) {
                        $rateId = $rateData['rate_id'];
                        break;
                    }
                }

                if ($rateId) {
                    $datum['rate_id'] = $rateId;
                } else {
                    unset($part[$key]);
                }
            }
        }

        return array_merge(...$parts);
    }

    /**
     * @param array $filteredRates
     * @throws \Zend_Db_Exception
     */
    private function insertRatesRegionIds(array $filteredRates = [])
    {
        if (empty($filteredRates)) {
            return;
        }

        $ratesCollection = $this->rateCollectionFactory->create();
        $conn            = $ratesCollection->getConnection();

        // Region Id
        $ratesWithFilledField = [];
        $field                = 'region_id';
        foreach ($filteredRates as $rate) {
            if (!empty($rate[$field]) && $rate[$field] !== '') {
                $ratesWithFilledField[] = $rate;
            }
        }

        if (!empty($ratesWithFilledField)) {
            $rateRegionIdRelationData = $this->getRateRelationData(
                $ratesWithFilledField,
                'region_id',
                'region_id',
                'rate_id',
                'rate_id'
            );
            $this->insertData(
                $rateRegionIdRelationData,
                $conn,
                $ratesCollection->getTable(RateInterface::RATE_REGION_ID_TABLE_NAME)
            );
        }
    }

    /**
     * @param array $filteredRates
     * @throws \Zend_Db_Exception
     */
    private function insertRatesRegions(array $filteredRates = [])
    {
        if (empty($filteredRates)) {
            return;
        }

        $ratesCollection = $this->rateCollectionFactory->create();
        $conn            = $ratesCollection->getConnection();

        // Region
        $ratesWithFilledField = [];
        $field                = 'region';
        foreach ($filteredRates as $rate) {
            if (!empty($rate[$field]) && $rate[$field] !== '') {
                $ratesWithFilledField[] = $rate;
            }
        }

        if (!empty($ratesWithFilledField)) {
            $rateRegionRelationData = $this->getRateRelationData(
                $ratesWithFilledField,
                'region',
                'region',
                'rate_id',
                'rate_id'
            );
            $this->insertData(
                $rateRegionRelationData,
                $conn,
                $ratesCollection->getTable(RateInterface::RATE_REGION_TABLE_NAME)
            );
        }
    }

    /**
     * @param array $filteredRates
     * @throws \Zend_Db_Exception
     */
    private function insertRatesPlainZipCodes(array $filteredRates = [])
    {
        if (empty($filteredRates)) {
            return;
        }

        $ratesCollection = $this->rateCollectionFactory->create();
        $conn            = $ratesCollection->getConnection();

        // Plain zip codes
        $ratesWithFilledField = [];
        $field                = 'plain_zip_codes';
        foreach ($filteredRates as $rate) {
            if (!empty($rate[$field]) && $rate[$field] !== '') {
                $ratesWithFilledField[] = $rate;
            }
        }
        if (!empty($ratesWithFilledField)) {
            $zipsRelationData = $this->getZipsData($ratesWithFilledField);
            $this->insertData(
                $zipsRelationData,
                $conn,
                $ratesCollection->getTable(RateInterface::RATE_ZIPS_TABLE_NAME)
            );
        }
    }

    /**
     * Get zip data for rate
     *
     * @param array $data
     * @return array
     */
    private function getZipsData(array $data)
    {
        if (empty($data)) {
            return [];
        }

        $data = $this->fillRatesWithRateId($data);

        $relationsData = [];
        $errors        = 0;
        foreach ($data as $datum) {
            $values = explode(',', $datum['plain_zip_codes']);
            foreach ($values as $value) {
                $cleanValue = trim($value);
                $inverted   = stripos($cleanValue, '!') === 0;
                if ($inverted) {
                    $cleanZip = str_ireplace('!', '', $cleanValue);
                } else {
                    $cleanZip = $cleanValue;
                }

                if (empty($datum['rate_id'])) {
                    $errors++;
                    continue;
                }

                $relationsData[] = [
                    'rate_id'  => $datum['rate_id'],
                    'zip'      => $cleanZip,
                    'inverted' => $inverted
                ];
            }
        }

        return $relationsData;
    }

    /**
     * @param array $filteredRates
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function insertRatesZipCodeDiapasons(array $filteredRates = [])
    {
        if (empty($filteredRates)) {
            return;
        }

        // Zip code diapasons
        $ratesWithFilledField = [];
        $field                = 'zip_code_diapasons';
        foreach ($filteredRates as $rate) {
            if (!empty($rate[$field]) && $rate[$field] !== '') {
                $ratesWithFilledField[] = $rate;
            }
        }

        if (!empty($ratesWithFilledField)) {
            $zipDiapasonsRelationDataByFormats = $this->getZipDiapasonsData($ratesWithFilledField);
            foreach ($zipDiapasonsRelationDataByFormats as $format => $diapasonsData) {
                $formatter = $this->zipCodeManager->getFormatter($format);
                $formatter->bulkInsertUpdate($diapasonsData);
            }
        }
    }

    /**
     * Prepare zip diapasons data before insert
     *
     * @param array $data
     * @return array
     */
    private function getZipDiapasonsData(array $data)
    {
        if (empty($data)) {
            return [];
        }

        $data = $this->fillRatesWithRateId($data);

        $relationsData = [];
        foreach ($data as $datum) {
            if (!empty($datum['zip_format'])) {
                $format = $datum['zip_format'];
            }

            $diapasons = explode(',', $datum['zip_code_diapasons']);
            foreach ($diapasons as $diapason) {
                $cleanDiapason = trim($diapason);
                $inverted      = stripos($cleanDiapason, '!') === 0;
                if ($inverted) {
                    $cleanDiapason = str_ireplace('!', '', $cleanDiapason);
                }
                $diapasonArray = explode('-', $cleanDiapason);
                if (empty($diapasonArray[0]) || empty($diapasonArray[1])) {
                    // Mismatch arguments
                    continue;
                }

                $from = $diapasonArray[0];
                $to   = $diapasonArray[1];

                $cleanFrom = trim($from);
                $cleanTo   = trim($to);

                if (empty($format)) {
                    $formatFrom = $this->zipCodeManager->detectFormat($cleanFrom);
                    $formatTo   = $this->zipCodeManager->detectFormat($cleanTo);
                    if ($formatFrom !== $formatTo) {
                        continue; // Mismatch formats
                    } else {
                        $format = $formatFrom;
                    }
                }

                $relationsData[$format][] = [
                    'rate_id'  => $datum['rate_id'],
                    'from'     => $cleanFrom,
                    'to'       => $cleanTo,
                    'inverted' => $inverted
                ];
            }
        }

        return $relationsData;
    }
}