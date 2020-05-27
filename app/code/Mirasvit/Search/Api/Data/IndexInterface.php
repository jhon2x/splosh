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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Api\Data;

interface IndexInterface
{
    const TABLE_NAME = 'mst_search_index';

    const ID = 'index_id';
    const IDENTIFIER = 'identifier';
    const TITLE = 'title';
    const POSITION = 'position';
    const ATTRIBUTES_SERIALIZED = 'attributes_serialized';
    const PROPERTIES_SERIALIZED = 'properties_serialized';
    const STATUS = 'status';
    const IS_ACTIVE = 'is_active';

    const STATUS_READY = 1;
    const STATUS_INVALID = 0;

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @param string $input
     * @return $this
     */
    public function setIdentifier($input);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $input
     * @return $this
     */
    public function setTitle($input);

    /**
     * @return number
     */
    public function getPosition();

    /**
     * @param number $input
     * @return $this
     */
    public function setPosition($input);

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @param array $value
     * @return $this
     */
    public function setAttributes($value);

    /**
     * @return array
     */
    public function getProperties();

    /**
     * @param array $value
     * @return $this
     */
    public function setProperties($value);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $input
     * @return $this
     */
    public function setStatus($input);

    /**
     * @return number
     */
    public function getIsActive();

    /**
     * @param number $input
     * @return $this
     */
    public function setIsActive($input);

    /**
     * @param string $key
     * @return string
     */
    public function getProperty($key);

    /**
     * @param string $key
     * @return mixed|array
     */
    public function getData($key = null);

    /**
     * @param string|array $key
     * @param string|int|array $value
     * @return $this
     */
    public function setData($key, $value = null);

    /**
     * @param array $data
     * @return $this
     */
    public function addData(array $data);
}
