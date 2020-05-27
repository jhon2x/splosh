<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use MageWorx\ShippingRules\Api\MethodRepositoryInterface;
use MageWorx\ShippingRules\Model\Carrier as CarrierModel;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\StringUtils;
use MageWorx\ShippingRules\Helper\Data as Helper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver;
use MageWorx\ShippingRules\Api\Data\RateInterface;
use MageWorx\ShippingRules\Model\ZipCodeManager;

/**
 * Class Rate
 */
class Rate extends AbstractResourceModel
{
    /**
     * Store associated with method entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [
        'store' => [
            'associations_table' => CarrierModel::RATE_TABLE_NAME . '_store',
            'ref_id_field'       => 'entity_id',
            'entity_id_field'    => 'store_id',
        ]
    ];

    /**
     * @var array
     */
    protected $priceFields = [
        'price_from',
        'price_to',
        'price',
        'price_per_product',
        'price_per_item',
        'price_per_weight'
    ];

    /**
     * @var MethodRepositoryInterface
     */
    protected $methodRepository;

    /**
     * @var StoreResolver
     */
    protected $storeResolver;

    /**
     * List of a fields which store a value as comma separated string
     * converts to array after load
     *
     * @see \MageWorx\ShippingRules\Model\ResourceModel\Rate::serializeFields()
     * @see \MageWorx\ShippingRules\Model\ResourceModel\Rate::unserializeFields()
     *
     * @var array
     */
    private $commaSeparatedFields = [

    ];

    /**
     * @var ZipCodeManager
     */
    private $zipCodeManager;

    /**
     * Rate constructor.
     *
     * @param Context $context
     * @param StringUtils $string
     * @param Helper $helper
     * @param StoreManagerInterface $storeManager
     * @param StoreResolver $storeResolver
     * @param MethodRepositoryInterface $methodRepository
     * @param ZipCodeManager $zipCodeManager
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        StringUtils $string,
        Helper $helper,
        StoreManagerInterface $storeManager,
        StoreResolver $storeResolver,
        MethodRepositoryInterface $methodRepository,
        ZipCodeManager $zipCodeManager,
        $connectionName = null
    ) {
        $this->storeResolver = $storeResolver;
        parent::__construct($context, $string, $helper, $storeManager, $connectionName);
        $this->methodRepository = $methodRepository;
        $this->zipCodeManager   = $zipCodeManager;
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CarrierModel::RATE_TABLE_NAME, 'rate_id');
    }

    /**
     * @param string|int $zip
     * @return \MageWorx\ShippingRules\Api\ZipCodeFormatInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function detectZipFormatter($zip)
    {
        return $this->zipCodeManager->detectFormatter($zip);
    }

    /**
     * @param string|int $zip
     * @return string
     */
    public function detectZipFormat($zip)
    {
        return $this->zipCodeManager->detectFormat($zip);
    }

    /**
     * @param string $zipFormat
     * @return string
     * @throws LocalizedException
     */
    public function getZipDiapasonTableAliasByFormat($zipFormat)
    {
        return $this->zipCodeManager->getTableAlias($zipFormat);
    }

    /**
     * Un-serialize serializable object fields
     *
     * @param AbstractModel $object
     * @return AbstractModel
     */
    public function unserializeFields(AbstractModel $object)
    {
        parent::unserializeFields($object);
        foreach ($this->commaSeparatedFields as $field) {
            if (is_array($object->getData($field))) {
                continue;
            } elseif ($object->getData($field)) {
                $object->setData($field, explode(',', $object->getData($field)));
            } else {
                $object->setData($field, []);
            }
        }
        // Workaround for the old values (not an array) @ver 2.1.1+
        $countryId = $object->getData('country_id');
        if (!is_array($countryId)) {
            if ($countryId) {
                $object->setData('country_id', [$countryId]);
            } else {
                $object->setData('country_id', []);
            }
        }
        $regionId = $object->getData('region_id');
        if (!is_array($regionId)) {
            if ($regionId) {
                $object->setData('region_id', [$regionId]);
            } else {
                $object->setData('region_id', []);
            }
        }

        return $object;
    }

    /**
     * Serialize serializable fields of the object
     *
     * @param AbstractModel $object
     * @return AbstractModel
     */
    public function serializeFields(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::serializeFields($object);
        $this->makeCommaSeparatedFields($object);

        return $object;
    }

    /**
     * Make comma separated fields
     *
     * @param AbstractModel $object
     */
    protected function makeCommaSeparatedFields(\Magento\Framework\Model\AbstractModel $object)
    {
        foreach ($this->commaSeparatedFields as $field) {
            if (is_array($object->getData($field))) {
                $object->setData($field, implode(',', $object->getData($field)));
            }
        }
    }

    /**
     * @param AbstractModel|\MageWorx\ShippingRules\Api\Data\RateInterface $object
     * @return $this|AbstractResourceModel
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        parent::_beforeSave($object);
        $this->validateModel($object);

        return $this;
    }

    /**
     * Validate model required fields
     *
     * @param AbstractModel|\MageWorx\ShippingRules\Api\Data\RateInterface $object
     * @throws LocalizedException
     */
    public function validateModel(AbstractModel $object)
    {
        if (!$object->getRateCode()) {
            throw new LocalizedException(__('Rate Code is required'));
        }

        if (!$object->getMethodCode()) {
            throw new LocalizedException(__('Corresponding Method Code is required'));
        }
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _afterLoad(AbstractModel $object)
    {
        /** @var \MageWorx\ShippingRules\Model\Carrier\Method\Rate $object */
        parent::_afterLoad($object);

        $storeId = $this->storeResolver->getCurrentStoreId();

        try {
            $label = $object->getStoreLabel($storeId);
        } catch (LocalizedException $localizedException) {
            $label = null;
        }

        if (!empty($label)) {
            $object->setTitle($label);
        }

        // Get and store in the object plain zip codes
        $plainZipCodes = $this->getLinkedData(
            'rate_id',
            $object->getId(),
            RateInterface::RATE_ZIPS_TABLE_NAME,
            [
                'zip',
                'inverted'
            ]
        );
        $object->setPlainZipCodes($plainZipCodes);

        // Get and store in object zip code diapasons
        $zipFormat            = $object->getZipFormat();
        $zipDiapasonTableName = $this->getZipDiapasonFormatModel($zipFormat)->getTableName();
        $zipCodesDiapason     = $this->getLinkedData(
            'rate_id',
            $object->getId(),
            $zipDiapasonTableName,
            [
                'from',
                'to',
                'inverted'
            ]
        );
        $object->setZipCodeDiapasons($zipCodesDiapason);

        // Get and store in object country ids
        $countryIds    = [];
        $countryIdsRaw = $this->getLinkedData(
            'rate_id',
            $object->getId(),
            RateInterface::RATE_COUNTRY_TABLE_NAME,
            [
                'country_code'
            ]
        );
        if (!empty($countryIdsRaw)) {
            foreach ($countryIdsRaw as $countryIdRaw) {
                $countryIds[] = $countryIdRaw['country_code'];
            }
            $countryIds = array_unique($countryIds);
            $object->setCountryId($countryIds);
        }

        // Get and store in object region ids
        $regionIds    = [];
        $regionIdsRaw = $this->getLinkedData(
            'rate_id',
            $object->getId(),
            RateInterface::RATE_REGION_ID_TABLE_NAME,
            [
                'region_id'
            ]
        );
        if (!empty($regionIdsRaw)) {
            foreach ($regionIdsRaw as $regionIdRaw) {
                $regionIds[] = $regionIdRaw['region_id'];
            }
            $regionIds = array_unique($regionIds);
            $object->setRegionId($regionIds);
        }

        // Get and store in object regions
        $regions    = [];
        $regionsRaw = $this->getLinkedData(
            'rate_id',
            $object->getId(),
            RateInterface::RATE_REGION_TABLE_NAME,
            [
                'region'
            ]
        );
        if (!empty($regionsRaw)) {
            foreach ($regionsRaw as $regionRaw) {
                $regions[] = $regionRaw['region'];
            }
            $regions = array_unique($regions);
            $object->setRegion($regions);
        }

        return $this;
    }

    /**
     * Get data from linked table by key
     *
     * @param string $keyName
     * @param mixed $keyValue
     * @param string $tableName
     * @param array $columns
     * @return array
     */
    private function getLinkedData($keyName, $keyValue, $tableName, array $columns)
    {
        $linkedTable = $this->getTable($tableName);
        $connection  = $this->getConnection();
        $select      = $connection->select();
        $select->from($linkedTable, $columns);
        $select->where($keyName . ' = ?', $keyValue);

        $data = $connection->fetchAll($select);

        return $data;
    }

    /**
     * @param string $zipFormat
     * @return \MageWorx\ShippingRules\Api\ZipCodeFormatInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getZipDiapasonFormatModel($zipFormat)
    {
        return $this->zipCodeManager->getFormatter($zipFormat);
    }

    /**
     * Serialize serializable fields of the object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    protected function _serializeFields(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_serializeFields($object);
        $this->makeCommaSeparatedFields($object);
    }

    /**
     * Save rate's associated store labels.
     *
     * @param AbstractModel|\MageWorx\ShippingRules\Api\Data\RateInterface $object
     * @return $this
     * @throws \Exception
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveCountryIds($object->getId(), $object->getCountryId());
        $this->saveRegionIds($object->getId(), $object->getRegionId());
        $this->saveRegions($object->getId(), $object->getRegion());
        $this->saveZipDiapasonsData($object, $object->getZipCodeDiapasons());
        $this->saveZipsData($object->getId(), $object->getPlainZipCodes());

        return parent::_afterSave($object);
    }

    /**
     * Save country ids in separate table
     *
     * @param int $objectId
     * @param string|array $inputData
     * @return $this
     * @throws \Exception
     */
    private function saveCountryIds($objectId, $inputData)
    {
        if (empty($objectId)) {
            return $this;
        }

        if (empty($inputData)) {
            $this->cleanLinkedTableData(RateInterface::RATE_COUNTRY_TABLE_NAME, 'rate_id', $objectId);

            return $this;
        }

        if (!is_array($inputData)) {
            $inputData = explode(',', $inputData);
        }

        $data = [];
        foreach ($inputData as $inputDatum) {
            $data[] = [
                'country_code' => $inputDatum,
                'rate_id'      => $objectId
            ];
        }

        if (empty($data)) {
            return $this;
        }

        $linkedTable = $this->getTable(RateInterface::RATE_COUNTRY_TABLE_NAME);
        $connection  = $this->getConnection();
        $connection->beginTransaction();
        try {
            // Clean data
            $connection->delete($linkedTable, sprintf('rate_id = %d', $objectId));
            // Add new data
            $connection->insertOnDuplicate($linkedTable, $data, ['rate_id', 'country_code']);
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $connection->commit();

        return $this;
    }

    /**
     * Clean all data by FK in linked table
     *
     * @param string $tableName @important without prefix!
     * @param string $likedKey
     * @param int $id
     * @throws \Exception
     */
    private function cleanLinkedTableData($tableName, $likedKey, $id)
    {
        $linkedTable = $this->getTable($tableName);
        $connection  = $this->getConnection();
        $connection->beginTransaction();
        try {
            // Clean data
            $connection->delete($linkedTable, sprintf('%s = %d', $likedKey, $id));
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $connection->commit();
    }

    /**
     * Save region ids in separate table
     *
     * @param int $objectId
     * @param string|array|null $inputData
     * @return $this
     * @throws \Exception
     */
    private function saveRegionIds($objectId, $inputData)
    {
        if (empty($objectId)) {
            return $this;
        }

        if (empty($inputData)) {
            $this->cleanLinkedTableData(RateInterface::RATE_REGION_ID_TABLE_NAME, 'rate_id', $objectId);

            return $this;
        }

        if (!is_array($inputData)) {
            $inputData = explode(',', $inputData);
        }

        $data = [];
        foreach ($inputData as $inputDatum) {
            $data[] = [
                'region_id' => $inputDatum,
                'rate_id'   => $objectId
            ];
        }

        if (empty($data)) {
            return $this;
        }

        $linkedTable = $this->getTable(RateInterface::RATE_REGION_ID_TABLE_NAME);
        $connection  = $this->getConnection();
        $connection->beginTransaction();
        try {
            // Clean data
            $connection->delete($linkedTable, sprintf('rate_id = %d', $objectId));
            // Add new data
            $connection->insertOnDuplicate($linkedTable, $data, ['rate_id', 'region_id']);
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $connection->commit();

        return $this;
    }

    /**
     * Save regions in separate table
     *
     * @param int $objectId
     * @param string|array|null $inputData
     * @return $this
     * @throws \Exception
     */
    private function saveRegions($objectId, $inputData)
    {
        if (empty($objectId)) {
            return $this;
        }

        if (empty($inputData)) {
            $this->cleanLinkedTableData(RateInterface::RATE_REGION_TABLE_NAME, 'rate_id', $objectId);

            return $this;
        }

        if (!is_array($inputData)) {
            $inputData = explode(',', $inputData);
        }

        $data = [];
        foreach ($inputData as $inputDatum) {
            $data[] = [
                'region'  => $inputDatum,
                'rate_id' => $objectId
            ];
        }

        if (empty($data)) {
            return $this;
        }

        $linkedTable = $this->getTable(RateInterface::RATE_REGION_TABLE_NAME);
        $connection  = $this->getConnection();
        $connection->beginTransaction();
        try {
            // Clean data
            $connection->delete($linkedTable, sprintf('rate_id = %d', $objectId));
            // Add new data
            $connection->insertOnDuplicate($linkedTable, $data, ['rate_id', 'region']);
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $connection->commit();

        return $this;
    }

    /**
     * Save zip diapasons data to the own table (linked by rate_id)
     *
     * @param \MageWorx\ShippingRules\Api\Data\RateInterface $object
     * @param array $data
     * @throws \Exception
     * @return void
     */
    private function saveZipDiapasonsData($object, $data)
    {
        $objectId             = $object->getRateId();
        $zipFormat            = $object->getZipFormat();
        $zipDiapasonModel     = $this->getZipDiapasonFormatModel($zipFormat);
        $zipDiapasonTableName = $zipDiapasonModel->getTableName();

        if (empty($data)) {
            $this->cleanLinkedTableData(
                $zipDiapasonTableName,
                'rate_id',
                $objectId
            );

            return;
        }

        $clearData = [];
        foreach ($data as $datum) {
            if (empty($datum['from'])) {
                continue;
            }
            if (empty($datum['to'])) {
                continue;
            }

            $datum['from'] = trim($datum['from']);
            $datum['to'] = trim($datum['to']);

            if (!$zipDiapasonModel->isSuitableZip($datum['from'])) {
                throw new LocalizedException(
                    __('Incorrect zip code format (%1). The %2 format required.', $datum['from'], $zipFormat)
                );
            }
            if (!$zipDiapasonModel->isSuitableZip($datum['to'])) {
                throw new LocalizedException(
                    __('Incorrect zip code format (%1). The %2 format required.', $datum['to'], $zipFormat)
                );
            }

            if (isset($datum['delete']) && $datum['delete'] === 'true') {
                continue;
            }
            if (empty($datum['inverted'])) {
                $datum['inverted'] = 0;
            }
            $clearData[] = [
                'rate_id'  => $objectId,
                'from'     => $datum['from'],
                'to'       => $datum['to'],
                'inverted' => $datum['inverted']
            ];
        }

        if (empty($clearData)) {
            $this->cleanLinkedTableData(
                $zipDiapasonTableName,
                'rate_id',
                $objectId
            );

            return;
        }

        $linkedTable = $this->getTable($zipDiapasonTableName);
        $connection  = $this->getConnection();
        $connection->beginTransaction();
        try {
            // Clean data
            $connection->delete($linkedTable, sprintf('rate_id = %d', $objectId));
            // Add new data
            $zipDiapasonModel->bulkInsertUpdate($clearData);
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $connection->commit();
    }

    /**
     * Save plain zips data to the own table (linked by rate_id)
     *
     * @param int $objectId
     * @param array $data
     * @throws \Exception
     * @return void
     */
    private function saveZipsData($objectId, $data)
    {
        if (empty($data)) {
            $this->cleanLinkedTableData(
                RateInterface::RATE_ZIPS_TABLE_NAME,
                'rate_id',
                $objectId
            );

            return;
        }

        $clearData = [];
        foreach ($data as $datum) {
            if (empty($datum['zip'])) {
                continue;
            }

            $datum['zip'] = trim($datum['zip']);

            if (empty($datum['inverted'])) {
                $datum['inverted'] = 0;
            }

            $clearData[] = [
                'rate_id'  => $objectId,
                'zip'      => $datum['zip'],
                'inverted' => $datum['inverted']
            ];
        }

        if (empty($clearData)) {
            $this->cleanLinkedTableData(
                RateInterface::RATE_ZIPS_TABLE_NAME,
                'rate_id',
                $objectId
            );

            return;
        }

        $linkedTable = $this->getTable(RateInterface::RATE_ZIPS_TABLE_NAME);
        $connection  = $this->getConnection();
        $connection->beginTransaction();
        try {
            // Clean data
            $connection->delete($linkedTable, sprintf('rate_id = %d', $objectId));
            // Add new data
            $connection->insertOnDuplicate($linkedTable, $clearData, ['rate_id', 'zip', 'inverted']);
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $connection->commit();
    }

    /**
     * Get store labels table
     *
     * @return string
     */
    protected function getStoreLabelsTable()
    {
        return $this->getTable(CarrierModel::RATE_LABELS_TABLE_NAME);
    }

    /**
     * Get reference id column name from the labels table
     *
     * @return string
     */
    protected function getStoreLabelsTableRefId()
    {
        return 'rate_id';
    }
}
