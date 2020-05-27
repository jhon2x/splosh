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
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Api\Data;

interface EventInterface
{
    const TABLE_NAME = 'mst_event_event';

    const ID = 'event_id';
    const IDENTIFIER = 'identifier';
    const KEY = 'key';
    const PARAMS_SERIALIZED = 'params_serialized';
    const STORE_ID = 'store_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $value
     * @return $this
     */
    public function setId($value);

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @param string $value
     * @return $this
     */
    public function setIdentifier($value);

    /**
     * @return string
     */
    public function getKey();

    /**
     * @param string $value
     * @return $this
     */
    public function setKey($value);

    /**
     * @return string
     */
    public function getParamsSerialized();

    /**
     * @param string $value
     * @return $this
     */
    public function setParamsSerialized($value);

    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $value
     * @return $this
     */
    public function setParams($value);

    /**
     * @param string $key
     * @return string
     **/
    public function getParam($key);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $value
     * @return $this
     */
    public function setStoreId($value);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setCreatedAt($value);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setUpdatedAt($value);
}