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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Api\Data;

interface ChainInterface
{
    const TABLE_NAME = 'mst_email_trigger_chain';

    const ID = 'chain_id';
    const TRIGGER_ID = 'trigger_id';
    const TEMPLATE_ID = 'template_id';
    const DELAY = 'delay';
    const DAY = 'day';
    const HOUR = 'hour';
    const MINUTE = 'minute';
    const SEND_FROM = 'send_from';
    const SEND_TO = 'send_to';
    const EXCLUDE_DAYS = 'exclude_days';
    const CROSS_SELLS_ENABLED = 'cross_sells_enabled';
    const CROSS_SELLS_TYPE_ID = 'cross_sells_type_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setTemplateId($id);

    /**
     * @return string
     */
    public function getTemplateId();

    /**
     * @return \Mirasvit\EmailDesigner\Api\Data\TemplateInterface
     */
    public function getTemplate();

    /**
     * @return int
     */
    public function getTriggerId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setTriggerId($id);

    /**
     * @return int
     */
    public function getDay();

    /**
     * @return int
     */
    public function getHour();

    /**
     * @return int
     */
    public function getMinute();

    /**
     * @return int
     */
    public function getSendFrom();

    /**
     * @return int
     */
    public function getSendTo();

    /**
     * @return int[]|string[]
     */
    public function getExcludeDays();

    /**
     * @param int $day
     *
     * @return $this
     */
    public function setDay($day);

    /**
     * @param int $hour
     *
     * @return $this
     */
    public function setHour($hour);

    /**
     * @param int $minute
     *
     * @return $this
     */
    public function setMinute($minute);

    /**
     * @param int $sendFrom
     *
     * @return $this
     */
    public function setSendFrom($sendFrom);

    /**
     * @param int $sendTo
     *
     * @return $this
     */
    public function setSendTo($sendTo);

    /**
     * @param int[] $excludeDays
     *
     * @return $this
     */
    public function setExcludeDays($excludeDays);

    /**
     * Whether the cross sell products appearance enabled or not.
     *
     * @return bool
     */
    public function getCrossSellsEnabled();

    /**
     * Get type of used cross sell source.
     *
     * @return string
     */
    public function getCrossSellsTypeId();

    /**
     * Get method name used to retrieve products.
     *
     * @return null|string
     */
    public function getCrossSellMethodName();

    /**
     * Calculate Scheduled At.
     *
     * @param int $time
     *
     * @return int
     */
    public function getScheduledAt($time);

    /**
     * If $key is empty, checks whether there's any data in the object
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     * @return bool
     */
    public function hasData($key = '');

    /**
     * Object data getter
     *
     * If $key is not defined will return all the data as an array.
     * Otherwise it will return value of the element specified by $key.
     * It is possible to use keys like a/b/c for access nested array data
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member. If data is the string - it will be explode
     * by new line character and converted to array.
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     */

    public function getData($key = '', $index = null);

    /**
     * Add data to the object.
     *
     * Retains previous data in the object.
     *
     * @param array $arr
     * @return $this
     */
    public function addData(array $arr);

    /**
     * Save object data
     *
     * @return $this
     * @throws \Exception
     * @deprecated
     */
    public function save();

    /**
     * Load object data
     *
     * @param integer $modelId
     * @param null|string $field
     * @return $this
     * @deprecated
     */
    public function load($modelId, $field = null);

    /**
     * Delete object from database
     *
     * @return $this
     * @throws \Exception
     * @deprecated
     */
    public function delete();

    /**
     * Convert object data into string with predefined format
     *
     * Will use $format as an template and substitute {{key}} for attributes
     *
     * @param string $format
     * @return string
     */
    public function toString($format = '');

    /**
     * Unset data from the object.
     *
     * @param null|string|array $key
     * @return $this
     */
    public function unsetData($key = null);
}
