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

interface QueueInterface
{
    const TABLE_NAME = 'mst_email_queue';

    /**
     * Entity Fields
     */
    const ID = 'queue_id';
    const TRIGGER_ID = 'trigger_id';
    const STATUS = 'status';
    const STATUS_MESSAGE = 'status_message';
    const CHAIN_ID = 'chain_id';
    const UNIQUE_KEY = 'uniq_key';
    const UNIQUE_HASH = 'uniq_hash';
    const SCHEDULED_AT = 'scheduled_at';
    const SENT_AT = 'sent_at';
    const ATTEMPTS_NUMBER = 'attemtps_number';
    const SENDER_EMAIL = 'sender_email';
    const SENDER_NAME = 'sender_name';
    const RECIPIENT_EMAIL = 'recipient_email';
    const RECIPIENT_NAME = 'recipient_name';
    const SUBJECT = 'subject';
    const CONTENT = 'content';
    const ARGS = 'args';
    const ARGS_SERIALIZED = 'args_serialized';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const HISTORY = 'history';


    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_CANCELED = 'canceled';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    const STATUS_ERROR = 'error';
    const STATUS_MISSED = 'missed';

    /**
     * @return int
     */
    public function getId();

    /**
     * Get trigger model.
     *
     * @return TriggerInterface
     */
    public function getTrigger();

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
     * Design template model.
     *
     * @return \Mirasvit\EmailDesigner\Api\Data\TemplateInterface
     */
    public function getTemplate();

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getChainId();

    /**
     * @param int $chainId
     *
     * @return $this
     */
    public function setChainId($chainId);

    /**
     * @return string
     */
    public function getUniqKey();

    /**
     * @param string $uniqKey
     *
     * @return $this
     */
    public function setUniqKey($uniqKey);

    /**
     * @return string
     */
    public function getUniqHash();

    /**
     * @param string $uniqHash
     *
     * @return $this
     */
    public function setUniqHash($uniqHash);

    /**
     * @return string
     */
    public function getScheduledAt();

    /**
     * @param string $scheduledAt
     * @return $this
     */
    public function setScheduledAt($scheduledAt);

    /**
     * @return string
     */
    public function getSentAt();

    /**
     * @param string $sentAt
     * @return $this
     */
    public function setSentAt($sentAt);

    /**
     * @return string
     */
    public function getAttemtpsNumber();

    /**
     * @param string $attemtpsNumber
     *
     * @return $this
     */
    public function setAttemtpsNumber($attemtpsNumber);

    /**
     * @return string
     */
    public function getSenderEmail();

    /**
     * @param string $senderEmail
     *
     * @return $this
     */
    public function setSenderEmail($senderEmail);

    /**
     * @return string
     */
    public function getSenderName();

    /**
     * @param string $senderName
     *
     * @return $this
     */
    public function setSenderName($senderName);

    /**
     * @return string
     */
    public function getRecipientEmail();

    /**
     * @param string $recipientEmail
     *
     * @return $this
     */
    public function setRecipientEmail($recipientEmail);

    /**
     * @return string
     */
    public function getRecipientName();

    /**
     * @param string $recipientName
     *
     * @return $this
     */
    public function setRecipientName($recipientName);

    /**
     * @return string
     */
    public function getContent();

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content);

    /**
     * @return string
     */
    public function getSubject();

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject);

    /**
     * Get queue args.
     *
     * @param string $key
     *
     * @return array|string
     */
    public function getArgs($key = null);

    /**
     * @param array $args
     *
     * @return $this
     */
    public function setArgs(array $args);

    /**
     * @return string
     */
    public function getArgsSerialized();

    /**
     * @param string $argsSerialized
     *
     * @return $this
     */
    public function setArgsSerialized($argsSerialized);

    /**
     * @return string
     */
    public function getHistory();

    /**
     * @param string $history
     *
     * @return $this
     */
    public function setHistory($history);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Send mail.
     *
     * @param bool $force
     *
     * @return $this
     */
    public function send($force = false);

    /**
     * Change status to pending.
     *
     * @param string $message
     * @return $this
     */
    public function pending($message = '');

    /**
     * Change status to delivery.
     *
     * @param string $message
     * @return $this
     */
    public function delivery($message = '');

    /**
     * Change status to miss.
     *
     * @param string $message
     * @return $this
     */
    public function miss($message = '');

    /**
     * Change status to unsubscribe.
     *
     * @param string $message
     * @return $this
     */
    public function unsubscribe($message = '');

    /**
     * Change status to cancel.
     *
     * @param string $message
     * @return $this
     */
    public function cancel($message = '');

    /**
     * Change status to error.
     *
     * @param string $message
     *
     * @return $this
     */
    public function error($message = '');

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
     * @param string     $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key = '', $index = null);

    /**
     * If $key is empty, checks whether there's any data in the object
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     * @return bool
     */
    public function hasData($key = '');

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

    /**
     * Get object original data
     *
     * @param string $key
     * @return mixed
     */
    public function getOrigData($key = null);
}
