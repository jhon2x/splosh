<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-report
 * @version   1.0.5
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchReport\Api\Data;

interface LogInterface
{
    const TABLE_NAME = 'mst_search_report_log';

    const ID = 'log_id';
    const QUERY = 'query';
    const FALLBACK_QUERY = 'fallback_query';
    const MISSPELL_QUERY = 'misspell_query';
    const RESULTS = 'results';
    const IP = 'ip';
    const SESSION = 'session';
    const CUSTOMER_ID = 'customer_id';
    const COUNTRY = 'country';
    const ORDER_ITEM_ID = 'order_item_id';
    const CLICKS = 'clicks';
    const SOURCE = 'source';
    const CREATED_AT = 'created_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getQuery();

    /**
     * @param string $value
     * @return $this
     */
    public function setQuery($value);

    /**
     * @return string
     */
    public function getFallbackQuery();

    /**
     * @param string $value
     * @return $this
     */
    public function setFallbackQuery($value);

    /**
     * @return string
     */
    public function getMisspellQuery();

    /**
     * @param string $value
     * @return $this
     */
    public function setMisspellQuery($value);

    /**
     * @return int
     */
    public function getResults();

    /**
     * @param int $value
     * @return $this
     */
    public function setResults($value);

    /**
     * @return string
     */
    public function getIp();

    /**
     * @param string $value
     * @return $this
     */
    public function setIp($value);

    /**
     * @return string
     */
    public function getSession();

    /**
     * @param string $value
     * @return $this
     */
    public function setSession($value);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $value
     * @return $this
     */
    public function setCustomerId($value);

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @param string $value
     * @return $this
     */
    public function setCountry($value);

    /**
     * @return string
     */
    public function getOrderItemId();

    /**
     * @param string $value
     * @return $this
     */
    public function setOrderItemId($value);

    /**
     * @return int
     */
    public function getClicks();

    /**
     * @param int $value
     * @return $this
     */
    public function setClicks($value);

    /**
     * @return string
     */
    public function getSource();

    /**
     * @param string $value
     * @return $this
     */
    public function setSource($value);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setCreatedAt($value);
}