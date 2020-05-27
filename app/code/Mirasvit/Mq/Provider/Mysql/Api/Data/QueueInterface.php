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
 * @package   mirasvit/module-message-queue
 * @version   1.0.9
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Mq\Provider\Mysql\Api\Data;

interface QueueInterface
{
    const TABLE_NAME = 'mst_message_queue';

    const STATUS_NEW = 'new';
    const STATUS_COMPLETE = 'complete';

    const ID = 'message_id';
    const QUEUE_NAME = 'queue_name';
    const BODY = 'body';
    const STATUS = 'status';
    const RETRIES = 'retries';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getQueueName();

    /**
     * @param string $value
     * @return $this
     */
    public function setQueueName($value);

    /**
     * @return string
     */
    public function getBody();

    /**
     * @param string $value
     * @return $this
     */
    public function setBody($value);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $value
     * @return $this
     */
    public function setStatus($value);

    /**
     * @return int
     */
    public function getRetries();

    /**
     * @param int $value
     * @return $this
     */
    public function setRetries($value);
}