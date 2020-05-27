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



namespace Mirasvit\Event\Api\Data\Event;

use Magento\Framework\DataObject;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\Api\Data\EventInterface;

interface InstanceEventInterface
{
    const PARAM_CREATED_AT = 'created_at';
    const PARAM_EXPIRE_AFTER = 'expire_after';
    const PARAM_STORE_ID = 'store_id';
    const PARAM_CUSTOMER_ID = 'customer_id';
    const PARAM_CUSTOMER_EMAIL = 'customer_email';
    const PARAM_CUSTOMER_NAME = 'customer_name';

    /**
     * @return array
     */
    public function getEvents();

    /**
     * @return EventDataInterface[]
     */
    public function getEventData();

    /**
     * @param array $params
     * @return string
     */
    public function toString($params);

    /**
     * @param string $eventIdentifier
     * @param array $ruleConditions
     * @return EventInterface[]
     */
    public function check($eventIdentifier, $ruleConditions);

    /**
     * @param array $params
     * @return array
     */
    public function expand($params);

    /**
     * Check whether event can be used or not.
     *
     * @return bool
     */
    public function isActive();
}
