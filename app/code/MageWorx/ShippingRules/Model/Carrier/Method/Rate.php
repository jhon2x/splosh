<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Carrier\Method;

use MageWorx\ShippingRules\Api\Data\RateInterface;
use MageWorx\ShippingRules\Api\ImportExportEntity;
use Magento\Framework\DataObject;
use MageWorx\ShippingRules\Model\Carrier\AbstractModel;
use Magento\Quote\Model\Quote\Address\RateRequest;
use MageWorx\ShippingRules\Model\ResourceModel\Rate as RateResource;

/**
 * Class Rate
 *
 * @method bool hasStoreLabels()
 * @method RateResource _getResource()
 * @method boolean hasStoreIds()
 *
 */
class Rate extends AbstractModel implements RateInterface, ImportExportEntity
{
    const CURRENT_RATE = 'current_rate';

    const PRICE_CALCULATION_OVERWRITE = 0;
    const PRICE_CALCULATION_SUM       = 1;

    const MULTIPLE_RATES_PRICE_CALCULATION_MAX_PRIORITY = 0;
    const MULTIPLE_RATES_PRICE_CALCULATION_MAX_PRICE    = 1;
    const MULTIPLE_RATES_PRICE_CALCULATION_MIN_PRICE    = 2;
    const MULTIPLE_RATES_PRICE_CALCULATION_SUM_UP       = 3;

    const DELIMITER = ',';
    const MAX_ZIP   = 9999999999;
    const MIN_ZIP   = 0;

    /**
     * Columns which will be ignored during import/export process
     *
     * @see \MageWorx\ShippingRules\Model\Carrier\AbstractModel::getIgnoredColumnsForImportExport()
     */
    const IMPORT_EXPORT_IGNORE_COLUMNS = [
        'created_at',
        'updated_at',
        'rate_id',
        'method_id',
        'store_labels',
        'edt_store_specific_message',
        'custom_attribute',
        'custom_attributes',
    ];

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageworx_shippingrules_rate';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rate';

    /**
     * @var bool
     */
    protected $methodPriceWasAdded = false;

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MageWorx\ShippingRules\Model\ResourceModel\Rate');
        $this->setIdFieldName('rate_id');
    }

    /**
     * Processing object before save data
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        /**
         * Prepare store Ids if applicable and if they were set as string in comma separated format.
         * Backwards compatibility.
         */
        if ($this->hasStoreIds()) {
            $storeIds = $this->getStoreIds();
            if (!empty($storeIds)) {
                $this->setStoreIds($storeIds);
            }
        }

        parent::beforeSave();

        return $this;
    }

    /**
     * Get rule associated store Ids
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreIds()
    {
        if (!$this->hasStoreIds()) {
            $storeIds = $this->_getResource()->getStoreIds($this->getId());
            $this->setData('store_ids', (array)$storeIds);
        }

        return $this->getData('store_ids');
    }

    /**
     * Set associated store Ids
     *
     * @param array $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setStoreIds($value)
    {
        return $this->setData('store_ids', $value);
    }

    /**
     * Set if not yet and retrieve method store labels
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreLabels()
    {
        if (!$this->hasStoreLabels()) {
            $labels = $this->_getResource()->getStoreLabels($this->getId());
            $this->setStoreLabels($labels);
        }

        return $this->_getData('store_labels');
    }

    /**
     * Set store specific labels (title)
     *
     * @param array $storeLabels
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setStoreLabels($storeLabels = [])
    {
        return $this->setData('store_labels', $storeLabels);
    }

    /**
     * Validate model data
     *
     * @param DataObject $dataObject
     * @return bool|array
     */
    public function validateData(DataObject $dataObject)
    {
        $errors = [];

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $method
     * @param RateRequest $request
     * @param \MageWorx\ShippingRules\Model\Carrier\Method $methodData
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyRateToMethod(
        \Magento\Quote\Model\Quote\Address\RateResult\Method $method,
        RateRequest $request,
        \MageWorx\ShippingRules\Model\Carrier\Method $methodData
    ) {
        $result = $this->getCalculatedPrice($request, $methodData);
        // Sum up rate prices
        if ($methodData->getMultipleRatesPrice() === Rate::MULTIPLE_RATES_PRICE_CALCULATION_SUM_UP) {
            $result += (float)$method->getPrice();
        }

        if ($methodData->getMaxPriceThreshold() !== null &&
            $methodData->getMaxPriceThreshold() > 0 &&
            $result > $methodData->getMaxPriceThreshold()
        ) {
            $method->setPrice($methodData->getMaxPriceThreshold());
        } elseif ($methodData->getMinPriceThreshold() !== null &&
            $result < $methodData->getMinPriceThreshold() &&
            $methodData->getMinPriceThreshold() > 0
        ) {
            $method->setPrice($methodData->getMinPriceThreshold());
        } else {
            $method->setPrice($result);
        }

        // Change method title (if it is allowed by a method config)
        if ($methodData->getReplaceableTitle()) {
            if ($this->getStoreLabel()) {
                $method->setMethodTitle($this->getStoreLabel());
            } elseif ($this->getTitle()) {
                $method->setMethodTitle($this->getTitle());
            }
        }

        // Change Estimated Delivery time
        if ($methodData->isNeedToDisplayEstimatedDeliveryTime() && $methodData->getReplaceableEstimatedDeliveryTime()) {
            $methodData->setEstimatedDeliveryTimeMinByRate($this->getEstimatedDeliveryTimeMin());
            $methodData->setEstimatedDeliveryTimeMaxByRate($this->getEstimatedDeliveryTimeMax());
        }

        return $method;
    }

    /**
     * Get calculated rate's price
     *
     * @param RateRequest $request
     * @param \MageWorx\ShippingRules\Model\Carrier\Method $methodData
     * @return mixed|number
     */
    public function getCalculatedPrice(
        RateRequest $request,
        \MageWorx\ShippingRules\Model\Carrier\Method $methodData
    ) {
        $requestItemsCount    = 0;
        $requestProductsCount = 0;
        foreach ($request->getAllItems() as $requestItem) {
            if ($requestItem->getParentItemId()) {
                continue;
            }
            $requestItemsCount    += 1;
            $requestProductsCount += (float)$requestItem->getQty();
        }
        $requestItemsCost = $this->calculateItemsTotalPrice($request->getAllItems());

        $price['base_price']          = $this->getPrice();
        $price['per_product']         = $requestProductsCount * $this->getPricePerProduct();
        $price['per_item']            = $requestItemsCount * $this->getPricePerItem();
        $price['percent_per_product'] = $requestProductsCount * $this->getPricePercentPerProduct() / 100;
        $price['percent_per_item']    = $requestItemsCount * $this->getPricePercentPerItem() / 100;
        $price['item_price_percent']  = $requestItemsCost * $this->getItemPricePercent() / 100;
        $price['per_weight']          = $request->getPackageWeight() * $this->getPricePerWeight();

        $result = array_sum($price);
        // Method price could be added only once
        if ($this->getRateMethodPrice() == self::PRICE_CALCULATION_SUM && !$this->methodPriceWasAdded) {
            $this->methodPriceWasAdded = true;
            $result                    += (float)$methodData->getData('price');
        }

        return $result;
    }

    /**
     * @param array $items
     * @return float
     */
    public function calculateItemsTotalPrice($items)
    {
        $totalPrice = 0.0;
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            /**
             * @TODO: Should it be calculated with tax? or without discount?
             * Maybe we need a setting in the module config.
             */
            $totalPrice += (float)$item->getBaseRowTotal();
        }

        return $totalPrice;
    }

    /**
     * Get rates price
     *
     * @return float
     */
    public function getPrice()
    {
        return (float)$this->getData('price');
    }

    /**
     * Get rates price per each product in cart
     *
     * @return float
     */
    public function getPricePerProduct()
    {
        return (float)$this->getData('price_per_product');
    }

    /**
     * Get rates price per each item in cart
     *
     * @return float
     */
    public function getPricePerItem()
    {
        return (float)$this->getData('price_per_item');
    }

    /**
     * Get rates price percent per each product in cart
     *
     * @return float
     */
    public function getPricePercentPerProduct()
    {
        return (float)$this->getData('price_percent_per_product');
    }

    /**
     * Get rates price percent per each item in cart
     *
     * @return float
     */
    public function getPricePercentPerItem()
    {
        return (float)$this->getData('price_percent_per_item');
    }

    /**
     * Get item price percent
     *
     * @return float
     */
    public function getItemPricePercent()
    {
        return (float)$this->getData('item_price_percent');
    }

    /**
     * Price per each unit of weight
     *
     * @return float
     */
    public function getPricePerWeight()
    {
        return (float)$this->getData('price_per_weight');
    }

    /**
     * Get price calculation method
     *
     * @return int
     */
    public function getRateMethodPrice()
    {
        return $this->getData('rate_method_price');
    }

    /**
     * Retrieve rate name
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * Min estimated delivery time (usd to overwrite method value, visible at checkout & cart)
     *
     * @return float
     */
    public function getEstimatedDeliveryTimeMin()
    {
        $value = (float)$this->getData('estimated_delivery_time_min');

        return $value;
    }

    /**
     * Max estimated delivery time (usd to overwrite method value, visible at checkout & cart)
     *
     * @return float
     */
    public function getEstimatedDeliveryTimeMax()
    {
        $value = (float)$this->getData('estimated_delivery_time_max');

        return $value;
    }

    /**
     * Retrieve rate ID
     *
     * @return int
     */
    public function getRateId()
    {
        return $this->getData('rate_id');
    }

    /**
     * Get priority of the rate (sort order)
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->getData('priority');
    }

    /**
     * Check is rate active
     *
     * @return int|bool
     */
    public function getActive()
    {
        return $this->getData('active');
    }

    /**
     * Retrieve corresponding country id
     *
     * @return array
     */
    public function getCountryId()
    {
        $countryId = $this->getData('country_id');
        if (!$countryId) {
            return [];
        }

        return $countryId;
    }

    /**
     * Get region plain name
     *
     * @return array
     */
    public function getRegion()
    {
        $region = $this->getData('region');
        if (!$region) {
            return [];
        }

        return $region;
    }

    /**
     * Get id of region
     *
     * @return array
     */
    public function getRegionId()
    {
        $regionId = $this->getData('region_id');
        if (!$regionId) {
            return [];
        }

        return $regionId;
    }

    /**
     * Get conditions price from
     *
     * @return float
     */
    public function getPriceFrom()
    {
        return (float)$this->getData('price_from');
    }

    /**
     * Get conditions price to
     *
     * @return float
     */
    public function getPriceTo()
    {
        return (float)$this->getData('price_to');
    }

    /**
     * Get conditions qty from
     *
     * @return float
     */
    public function getQtyFrom()
    {
        return (float)$this->getData('qty_from');
    }

    /**
     * Get conditions qty to
     *
     * @return float
     */
    public function getQtyTo()
    {
        return (float)$this->getData('qty_to');
    }

    /**
     * Get conditions weight from
     *
     * @return float
     */
    public function getWeightFrom()
    {
        return (float)$this->getData('weight_from');
    }

    /**
     * Get conditions weight to
     *
     * @return float
     */
    public function getWeightTo()
    {
        return (float)$this->getData('weight_to');
    }

    /**
     * Get created at date
     *
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * Get last updated date
     *
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    /**
     * Get corresponding method code (relation)
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->getData('method_code');
    }

    /**
     * Set corresponding method code
     *
     * @param string $code
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setMethodCode($code)
    {
        return $this->setData('method_code', $code);
    }

    /**
     * Set rate ID
     *
     * @param int $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setRateId($value)
    {
        return $this->setData('rate_id', $value);
    }

    /**
     * Set priority of the rate (sort order)
     *
     * @param int $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPriority($value)
    {
        return $this->setData('priority', $value);
    }

    /**
     * Check is rate active
     *
     * @param bool $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setActive($value)
    {
        return $this->setData('active', $value);
    }

    /**
     * Set price calculation method
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setRateMethodPrice($value)
    {
        return $this->setData('rate_method_price', $value);
    }

    /**
     * Set rate name
     *
     * @param string $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setTitle($value)
    {
        return $this->setData('title', $value);
    }

    /**
     * Retrieve corresponding country id
     *
     * @param string[] $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setCountryId($value)
    {
        return $this->setData('country_id', $value);
    }

    /**
     * set region plain name
     *
     * @param string[] $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setRegion($value)
    {
        return $this->setData('region', $value);
    }

    /**
     * set id of region
     *
     * @param int[] $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setRegionId($value)
    {
        return $this->setData('region_id', $value);
    }

    /**
     * Set conditions price from
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPriceFrom($value)
    {
        return $this->setData('price_from', $value);
    }

    /**
     * Set conditions price to
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPriceTo($value)
    {
        return $this->setData('price_to', $value);
    }

    /**
     * Set conditions qty from
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setQtyFrom($value)
    {
        return $this->setData('qty_from', $value);
    }

    /**
     * Set conditions qty to
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setQtyTo($value)
    {
        return $this->setData('qty_to', $value);
    }

    /**
     * Set conditions weight from
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setWeightFrom($value)
    {
        return $this->setData('weight_from', $value);
    }

    /**
     * Set conditions weight to
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setWeightTo($value)
    {
        return $this->setData('weight_to', $value);
    }

    /**
     * Set rates price
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPrice($value)
    {
        return $this->setData('price', $value);
    }

    /**
     * Set rates price per each product in cart
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPricePerProduct($value)
    {
        return $this->setData('price_per_product', $value);
    }

    /**
     * Set rates price per each item in cart
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPricePerItem($value)
    {
        return $this->setData('price_per_item', $value);
    }

    /**
     * Set rates price percent per each product in cart
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPricePercentPerProduct($value)
    {
        return $this->setData('price_percent_per_product', $value);
    }

    /**
     * Set rates price percent per each item in cart
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPricePercentPerItem($value)
    {
        return $this->setData('price_percent_per_item', $value);
    }

    /**
     * Set item price percent
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setItemPricePercent($value)
    {
        return $this->setData('item_price_percent', $value);
    }

    /**
     * Price per each unit of weight
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPricePerWeight($value)
    {
        return $this->setData('price_per_weight', $value);
    }

    /**
     * Set created at date
     *
     * @param string $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setCreatedAt($value)
    {
        return $this->setData('created_at', $value);
    }

    /**
     * Set last updated date
     *
     * @param string $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setUpdatedAt($value)
    {
        return $this->setData('updated_at', $value);
    }

    /**
     * Min estimated delivery time (usd to overwrite method value, visible at checkout & cart)
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setEstimatedDeliveryTimeMin($value)
    {
        return $this->setData('estimated_delivery_time_min', $value);
    }

    /**
     * Max estimated delivery time (usd to overwrite method value, visible at checkout & cart)
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setEstimatedDeliveryTimeMax($value)
    {
        return $this->setData('estimated_delivery_time_max', $value);
    }

    /**
     * Retrieve rate code (used during import\export)
     *
     * @return string
     */
    public function getRateCode()
    {
        return $this->getData('rate_code');
    }

    /**
     * Set rate code (used during import\export)
     *
     * string $value
     *
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setRateCode($value)
    {
        return $this->setData('rate_code', $value);
    }

    /**
     * Is object has plain zip codes condition
     *
     * @return bool
     */
    public function hasPlainZipCodes()
    {
        $plainZipCodes = $this->getData('plain_zip_codes');

        return !empty($plainZipCodes);
    }

    /**
     * Get zip codes list (conditions)
     *
     * @return array
     */
    public function getPlainZipCodes()
    {
        $plainZipCodes = $this->getData('plain_zip_codes');
        if (empty($plainZipCodes)) {
            $plainZipCodes = [];
        }

        return $plainZipCodes;
    }

    /**
     * Set zip codes list (conditions)
     *
     * @param array $data
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPlainZipCodes(array $data)
    {
        return $this->setData('plain_zip_codes', $data);
    }

    /**
     * Get zip code diapasons list (conditions)
     *
     * @return array
     */
    public function getZipCodeDiapasons()
    {
        $zipCodeDiapasons = $this->getData('zip_code_diapasons');
        if (empty($zipCodeDiapasons)) {
            $zipCodeDiapasons = [];
        }

        return $zipCodeDiapasons;
    }

    /**
     * Set zip code diapasons list (conditions)
     *
     * @param array $data
     * @return Rate
     */
    public function setZipCodeDiapasons(array $data)
    {
        return $this->setData('zip_code_diapasons', $data);
    }

    /**
     * Is object has diapasons of zip codes
     *
     * @return bool
     */
    public function hasZipCodeDiapasons()
    {
        $zipCodesDiapason = $this->getData('zip_code_diapasons');

        return !empty($zipCodesDiapason);
    }

    /**
     * Get zip code validation mode:
     * 0 - no validation
     * 1 - plain zip codes
     * 2 - zip code diapasons
     *
     * @return int
     */
    public function getZipValidationMode()
    {
        return (int)$this->getData('zip_validation_mode');
    }

    /**
     * Set zip code validation mode:
     * 0 - no validation
     * 1 - plain zip codes
     * 2 - zip code diapasons
     *
     * @param int $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setZipValidationMode($value)
    {
        return $this->setData('zip_validation_mode', (int)$value);
    }

    /**
     * Get zip diapason format
     *
     * For details @see \MageWorx\ShippingRules\Model\ZipCodeManager
     *
     * @return string
     */
    public function getZipFormat()
    {
        return $this->getData('zip_format');
    }

    /**
     * Set zip diapason format
     *
     * For details @see \MageWorx\ShippingRules\Model\ZipCodeManager
     *
     * @param string $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setZipFormat($value)
    {
        return $this->setData('zip_format', $value);
    }
}
