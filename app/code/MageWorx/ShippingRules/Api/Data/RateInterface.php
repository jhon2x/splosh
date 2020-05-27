<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Api\Data;

/**
 * Interface RateInterface
 */
interface RateInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    const ENTITY_ID_FIELD_NAME      = 'rate_id';
    const RATE_COUNTRY_TABLE_NAME   = 'mageworx_shippingrules_rates_country';
    const RATE_REGION_TABLE_NAME    = 'mageworx_shippingrules_rates_region';
    const RATE_REGION_ID_TABLE_NAME = 'mageworx_shippingrules_rates_region_id';
    const RATE_ZIPS_TABLE_NAME      = 'mageworx_shippingrules_rates_zips';

    /**
     * Zip codes validation modes:
     * 0 - without validation by zip code (default);
     * 1 - plain zip codes validation (list of zip codes);
     * 2 - validate by zip code diapasons (list of diapasons);
     */
    const ZIP_VALIDATION_MODE_NONE     = 0;
    const ZIP_VALIDATION_MODE_PLAIN    = 1;
    const ZIP_VALIDATION_MODE_DIAPASON = 2;

    /**
     * Retrieve rate ID
     *
     * @return int
     */
    public function getRateId();

    /**
     * Retrieve rate code (used during import\export)
     *
     * @return string
     */
    public function getRateCode();

    /**
     * Get priority of the rate (sort order)
     *
     * @return int
     */
    public function getPriority();

    /**
     * Check is rate active
     *
     * @return int|bool
     */
    public function getActive();

    /**
     * Get price calculation method
     *
     * @return int
     */
    public function getRateMethodPrice();

    /**
     * Retrieve rate name
     *
     * @return string
     */
    public function getTitle();

    /**
     * Retrieve corresponding country id
     *
     * @return mixed[]
     */
    public function getCountryId();

    /**
     * Get region plain name
     *
     * @return mixed[]
     */
    public function getRegion();

    /**
     * Get id of region
     *
     * @return mixed[]
     */
    public function getRegionId();

    /**
     * Get conditions price from
     *
     * @return float
     */
    public function getPriceFrom();

    /**
     * Get conditions price to
     *
     * @return float
     */
    public function getPriceTo();

    /**
     * Get conditions qty from
     *
     * @return float
     */
    public function getQtyFrom();

    /**
     * Get conditions qty to
     *
     * @return float
     */
    public function getQtyTo();

    /**
     * Get conditions weight from
     *
     * @return float
     */
    public function getWeightFrom();

    /**
     * Get conditions weight to
     *
     * @return float
     */
    public function getWeightTo();

    /**
     * Get rates price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Get rates price per each product in cart
     *
     * @return float
     */
    public function getPricePerProduct();

    /**
     * Get rates price per each item in cart
     *
     * @return float
     */
    public function getPricePerItem();

    /**
     * Get rates price percent per each product in cart
     *
     * @return float
     */
    public function getPricePercentPerProduct();

    /**
     * Get rates price percent per each item in cart
     *
     * @return float
     */
    public function getPricePercentPerItem();

    /**
     * Get item price percent
     *
     * @return float
     */
    public function getItemPricePercent();

    /**
     * Price per each unit of weight
     *
     * @return float
     */
    public function getPricePerWeight();

    /**
     * Get created at date
     *
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * Get last updated date
     *
     * @return mixed
     */
    public function getUpdatedAt();

    /**
     * Min estimated delivery time (usd to overwrite method value, visible at checkout & cart)
     *
     * @return float
     */
    public function getEstimatedDeliveryTimeMin();

    /**
     * Max estimated delivery time (usd to overwrite method value, visible at checkout & cart)
     *
     * @return float
     */
    public function getEstimatedDeliveryTimeMax();


    /**
     * Get corresponding method code (relation)
     *
     * @return string
     */
    public function getMethodCode();

    /**
     * Get associated store Ids
     *
     * @return mixed[]
     */
    public function getStoreIds();

    /**
     * Get rate store specific labels
     *
     * @return mixed[]
     */
    public function getStoreLabels();

    /**
     * Get zip codes list (conditions)
     *
     * @return array
     */
    public function getPlainZipCodes();

    /**
     * Get zip code diapasons list (conditions)
     *
     * @return array
     */
    public function getZipCodeDiapasons();

    /**
     * Get zip code validation mode:
     * 0 - no validation
     * 1 - plain zip codes
     * 2 - zip code diapasons
     *
     * @return int
     */
    public function getZipValidationMode();

    /**
     * Get zip diapason format
     *
     * For details @see \MageWorx\ShippingRules\Model\ZipCodeManager
     *
     * @return string
     */
    public function getZipFormat();

    //____________________________________________ SETTERS _____________________________________________________________

    /**
     * Set rate ID
     *
     * @param int $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setRateId($value);

    /**
     * Set rate code (used during import\export)
     *
     * string $value
     *
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setRateCode($value);

    /**
     * Set priority of the rate (sort order)
     *
     * @param int $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPriority($value);

    /**
     * Check is rate active
     *
     * @param bool $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setActive($value);

    /**
     * Set price calculation method
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setRateMethodPrice($value);

    /**
     * Set rate name
     *
     * @param string $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setTitle($value);

    /**
     * Retrieve corresponding country id
     *
     * @param string[] $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setCountryId($value);

    /**
     * set region plain name
     *
     * @param string[] $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setRegion($value);

    /**
     * set id of region
     *
     * @param int[] $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setRegionId($value);

    /**
     * Set conditions price from
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPriceFrom($value);

    /**
     * Set conditions price to
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPriceTo($value);

    /**
     * Set conditions qty from
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setQtyFrom($value);

    /**
     * Set conditions qty to
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setQtyTo($value);

    /**
     * Set conditions weight from
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setWeightFrom($value);

    /**
     * Set conditions weight to
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setWeightTo($value);

    /**
     * Set rates price
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPrice($value);

    /**
     * Set rates price per each product in cart
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPricePerProduct($value);

    /**
     * Set rates price per each item in cart
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPricePerItem($value);

    /**
     * Set rates price percent per each product in cart
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPricePercentPerProduct($value);

    /**
     * Set rates price percent per each item in cart
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPricePercentPerItem($value);

    /**
     * Set item price percent
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setItemPricePercent($value);

    /**
     * Price per each unit of weight
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPricePerWeight($value);

    /**
     * Set created at date
     *
     * @param string $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setCreatedAt($value);

    /**
     * Set last updated date
     *
     * @param string $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setUpdatedAt($value);

    /**
     * Min estimated delivery time (usd to overwrite method value, visible at checkout & cart)
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setEstimatedDeliveryTimeMin($value);

    /**
     * Max estimated delivery time (usd to overwrite method value, visible at checkout & cart)
     *
     * @param float $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setEstimatedDeliveryTimeMax($value);

    /**
     * Set corresponding method code
     *
     * @param string $code
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setMethodCode($code);

    /**
     * Set associated store Ids
     *
     * @param mixed[] $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setStoreIds($value);

    /**
     * Set store specific labels (title)
     *
     * @param mixed[] $storeLabels
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setStoreLabels($storeLabels = []);

    /**
     * Set zip codes list (conditions)
     *
     * @param array $data
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setPlainZipCodes(array $data);

    /**
     * Set zip code diapasons list (conditions)
     *
     * @param array $data
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setZipCodeDiapasons(array $data);

    /**
     * Set zip code validation mode:
     * 0 - no validation
     * 1 - plain zip codes
     * 2 - zip code diapasons
     *
     * @param int $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setZipValidationMode($value);

    /**
     * Set zip diapason format
     *
     * For details @see \MageWorx\ShippingRules\Model\ZipCodeManager
     *
     * @param string $value
     * @return \MageWorx\ShippingRules\Api\Data\RateInterface
     */
    public function setZipFormat($value);

    //_______________________________________________ MISC _____________________________________________________________

    /**
     * Is object has plain zip codes condition
     *
     * @return bool
     */
    public function hasPlainZipCodes();

    /**
     * Is object has diapasons of zip codes
     *
     * @return bool
     */
    public function hasZipCodeDiapasons();
}
