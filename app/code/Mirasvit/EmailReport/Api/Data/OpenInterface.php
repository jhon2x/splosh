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
 * @package   mirasvit/module-email-report
 * @version   2.0.8
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Api\Data;

interface OpenInterface
{
    const TABLE_NAME = 'mst_emailreport_open';

    const ID          = 'open_id';
    const TRIGGER_ID  = 'trigger_id';
    const QUEUE_ID    = 'queue_id';
    const SESSION_ID  = 'session_id';
    const CREATED_AT  = 'created_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getTriggerId();

    /**
     * @param int $triggerId
     *
     * @return $this
     */
    public function setTriggerId($triggerId);

    /**
     * @return int
     */
    public function getQueueId();

    /**
     * @param int $queueId
     *
     * @return $this
     */
    public function setQueueId($queueId);

    /**
     * @return string
     */
    public function getSessionId();

    /**
     * @param string $sessionId
     *
     * @return $this
     */
    public function setSessionId($sessionId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return array
     */
    public function getData();

    /**
     * Overwrite data in the object.
     *
     * The $key parameter can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array  $key
     * @param mixed         $value
     *
     * @return $this
     */
    public function setData($key, $value = null);

    /**
     * Add data to the object.
     *
     * Retains previous data in the object.
     *
     * @param array $arr
     *
     * @return $this
     */
    public function addData(array $arr);
}
