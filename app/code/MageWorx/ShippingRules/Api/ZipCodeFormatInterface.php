<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Api;

use MageWorx\ShippingRules\Model\ResourceModel\Rate\Collection as RatesCollection;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Interface ZipCodeFormat
 *
 * @package MageWorx\ShippingRules\Api
 */
interface ZipCodeFormatInterface
{
    /**
     * Check is format suitable for zip-code
     *
     * @param string|int $zip
     * @return boolean
     */
    public function isSuitableZip($zip);

    /**
     * Filters rates collection by current format of zip code
     *
     * @param RatesCollection $collection
     * @param string|int $destinationZip
     * @return mixed
     */
    public function createConditionByZip(
        RatesCollection $collection,
        $destinationZip
    );

    /**
     * Creates own table (with indexes and foreign keys) where zip diapasons saved in desired format
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function createDbSchema(SchemaSetupInterface $setup);

    /**
     * Returns name of the table where zip code diapasons stored in that format
     *
     * @return string
     */
    public function getTableName();

    /**
     * Insert data
     *
     * @param array $data
     * @return int
     */
    public function bulkInsertUpdate(array $data);
}