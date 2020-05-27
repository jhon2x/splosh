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



namespace Mirasvit\Event\EventData;

use Magento\AdminNotification\Model\Inbox;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\InboxCondition;

class InboxData extends Inbox implements EventDataInterface
{
    const IDENTIFIER = 'inbox';

    const ID = 'notification_id';
    const DATE_ADDED = 'date_added';

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getConditionClass()
    {
        return InboxCondition::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return __('Admin Notification');
    }

    /**v
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'title'   => [
                'label' => __('Title'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'description'   => [
                'label' => __('Description'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'severity'    => [
                'label'   => __('Severity'),
                'type'    => self::ATTRIBUTE_TYPE_ENUM,
                'options' => $this->getSeverities()
            ],
            'url'   => [
                'label' => __('Url'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'is_read'   => [
                'label' => __('Is Read'),
                'type'  => self::ATTRIBUTE_TYPE_BOOL,
            ],
            'is_remove'   => [
                'label' => __('Is Removed'),
                'type'  => self::ATTRIBUTE_TYPE_BOOL,
            ],
            self::DATE_ADDED  => [
                'label' => __('Date Added'),
                'type'  => self::ATTRIBUTE_TYPE_DATE,
            ],
        ];
    }
}