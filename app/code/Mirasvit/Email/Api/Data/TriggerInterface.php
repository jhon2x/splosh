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

interface TriggerInterface
{
    const TABLE_NAME = 'mst_email_trigger';

    const ID = 'trigger_id';
    const CAMPAIGN_ID = 'campaign_id';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const STORE_IDS = 'store_ids';
    const IS_ACTIVE = 'is_active';
    const ACTIVE_FROM = 'active_from';
    const ACTIVE_TO = 'active_to';
    const TRIGGER_TYPE = 'trigger_type';
    const EVENT = 'event';
    const CANCELLATION_EVENT = 'cancellation_event';
    const RULE = 'rule';
    const RULE_SERIALIZED = 'rule_serialized';
    const SCHEDULE = 'schedule';
    const SENDER_EMAIL = 'sender_email';
    const SENDER_NAME = 'sender_name';
    const COPY_EMAIL = 'copy_email';
    const GA_SOURCE = 'ga_source';
    const GA_MEDIUM = 'ga_medium';
    const GA_TERM = 'ga_term';
    const GA_CONTENT = 'ga_content';
    const GA_NAME = 'ga_name';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    const IS_ADMIN = 'is_admin';
    const ADMIN_EMAIL = 'admin_email';

    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    const IS_ADMIN_ACTIVE = 1;
    const IS_ADMIN_DISABLED = 0;

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getRuleSerialized();

    /**
     * @param string $value
     * @return $this
     */
    public function setRuleSerialized($value);

    /**
     * @return string
     */
    public function getRule();

    /**
     * @param string $rule
     *
     * @return $this
     */
    public function setRule($rule);

    /**
     * @return string
     */
    public function getSchedule();

    /**
     * @param string $schedule
     *
     * @return $this
     */
    public function setSchedule($schedule);

    /**
     * @return string
     */
    public function getTriggerType();

    /**
     * @param string $triggerType
     *
     * @return $this
     */
    public function setTriggerType($triggerType);

    /**
     * List of all events (triggering + cancellation).
     *
     * @return string[]
     */
    public function getEvents();

    /**
     * @return string
     */
    public function getEvent();

    /**
     * @param string $event
     *
     * @return $this
     */
    public function setEvent($event);

    /**
     * List of triggering events.
     *
     * @return array
     */
    public function getTriggeringEvents();

    /**
     * List of cancellation events
     *
     * @return array
     */
    public function getCancellationEvents();

    /**
     * @return string[]
     */
    public function getCancellationEvent();

    /**
     * @param string[] $cancellationEvent
     *
     * @return $this
     */
    public function setCancellationEvent($cancellationEvent);

    /**
     * @return string
     */
    public function getGaSource();

    /**
     * @param string $source
     *
     * @return $this
     */
    public function setGaSource($source);

    /**
     * @return string
     */
    public function getGaMedium();

    /**
     * @param string $medium
     *
     * @return $this
     */
    public function setGaMedium($medium);

    /**
     * @return string
     */
    public function getGaName();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setGaName($name);

    /**
     * @return string
     */
    public function getGaTerm();

    /**
     * @param string $term
     *
     * @return $this
     */
    public function setGaTerm($term);

    /**
     * @return string
     */
    public function getGaContent();

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setGaContent($content);

    /**
     * @return int[]
     */
    public function getStoreIds();

    /**
     * @param int[] $storeIds
     *
     * @return $this
     */
    public function setStoreIds($storeIds);

    /**
     * @param int $campaignId
     *
     * @return $this
     */
    public function setCampaignId($campaignId);

    /**
     * @return int $campaignId
     */
    public function getCampaignId();

    /**
     * @return bool
     */
    public function getIsActive();

    /**
     * @param bool $isActive
     *
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Sender email specified for trigger or global
     *
     * @param int $storeId
     * @return string
     */
    public function getSenderEmail($storeId = 0);

    /**
     * @param string $senderEmail
     *
     * @return $this
     */
    public function setSenderEmail($senderEmail);

    /**
     * Sender name specified for trigger or global.
     *
     * @param int $storeId
     * @return string
     */
    public function getSenderName($storeId = 0);

    /**
     * @param string $senderName
     *
     * @return $this
     */
    public function setSenderName($senderName);

    /**
     * @return string
     */
    public function getActiveTo();

    /**
     * @param string $activeTo
     * @return $this
     */
    public function setActiveTo($activeTo);

    /**
     * @return string
     */
    public function getActiveFrom();

    /**
     * @param string $activeFrom
     * @return $this
     */
    public function setActiveFrom($activeFrom);

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
     * Determine if trigger is admin or customer.
     *
     * @return bool
     */
    public function getIsAdmin();

    /**
     * Set trigger is admin or customer.
     *
     * @param string $value
     *
     * @return bool
     */
    public function setIsAdmin($value);

    /**
     * Get trigger's admin email.
     *
     * @return string
     */
    public function getAdminEmail();

    /**
     * Set trigger's admin email.
     *
     * @param string $email
     *
     * @return string
     */
    public function setAdminEmail($email);

    /**
     * Determine if trigger is admin or customer.
     *
     * @return \Mirasvit\Email\Model\ResourceModel\Trigger\Chain\Collection|ChainInterface[]
     */
    public function getChainCollection();

    /**
     * Validate event args by trigger rules.
     *
     * @param array $args
     * @param bool  $force force validate
     *
     * @return bool
     */
    public function validateRules($args, $force = false);

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
     * Unset data from the object.
     *
     * @param null|string|array $key
     * @return $this
     */
    public function unsetData($key = null);
}
