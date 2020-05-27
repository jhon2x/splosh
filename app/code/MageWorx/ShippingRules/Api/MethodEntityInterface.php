<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Api;

use Magento\Framework\DataObject;

/**
 * Interface MethodEntityInterface
 *
 * Describes custom shipping method
 *
 * @package MageWorx\ShippingRules\Api
 */
interface MethodEntityInterface extends Data\MethodInterface
{
    /**
     * Validate model data
     *
     * @param DataObject $dataObject
     * @return bool|mixed[]
     */
    public function validateData(DataObject $dataObject);

    /**
     * Get Method label by specified store.
     * The $force flag make it applicable in the adminhtml area.
     *
     * @param \Magento\Store\Model\Store|int|bool|null $store
     * @return string|bool
     */
    public function getStoreLabel($store = null);

    /**
     * Set if not yet and retrieve method store labels
     *
     * @return mixed[]
     */
    public function getStoreLabels();

    /**
     * Initialize method model data from array.
     * Set store labels if applicable.
     *
     * @param mixed[] $data
     * @return $this
     */
    public function loadPost(array $data);

    /**
     * @param \MageWorx\ShippingRules\Model\ResourceModel\Rate\Collection $rates
     * @return $this
     */
    public function setRatesCollection(\MageWorx\ShippingRules\Model\ResourceModel\Rate\Collection $rates);

    /**
     * Display or not the estimated delivery time message
     *
     * @return bool
     */
    public function isNeedToDisplayEstimatedDeliveryTime();

    /**
     * Returns formatted estimated delivery time message
     * string will be formatted as $prefix + message + $ending
     *
     * @param string $prefix
     * @param string $ending
     * @return string
     */
    public function getEstimatedDeliveryTimeMessageFormatted($prefix = '', $ending = '');

    /**
     * Get min estimated delivery time by rate (overwritten default value)
     *
     * @return float
     */
    public function getEstimatedDeliveryTimeMinByRate();

    /**
     * Get max estimated delivery time by rate (overwritten default value)
     *
     * @return float
     */
    public function getEstimatedDeliveryTimeMaxByRate();

    /**
     * Set min estimated delivery time by rate (overwrite default value)
     *
     * @param float $value
     * @return $this
     */
    public function setEstimatedDeliveryTimeMinByRate($value);

    /**
     * Set max estimated delivery time by rate (overwrite default value)
     *
     * @param float $value
     * @return $this
     */
    public function setEstimatedDeliveryTimeMaxByRate($value);
}
