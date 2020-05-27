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



namespace Mirasvit\Event\EventData\Helpdesk;

use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\Helpdesk\MessageCondition;
use Magento\Framework\DataObject;

class MessageData extends DataObject implements EventDataInterface
{
    const ID = 'message_id';
    const IDENTIFIER = 'hdmx_message';

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getConditionClass()
    {
        return MessageCondition::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return __('Message');
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        $attributes = [
            'ticket.code' => [
                'label' => __('Ticket ID'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'ticket.subject' => [
                'label'   => __('Ticket Subject'),
                'type'    => self::ATTRIBUTE_TYPE_STRING,
            ],
            'ticket.backend_url' => [
                'label'   => __('Ticket URL'),
                'type'    => self::ATTRIBUTE_TYPE_STRING,
            ],
            'body_plain' => [
                'label' => __('Message'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'ticket.user_name' => [
                'label'   => __('User Name'),
                'type'    => self::ATTRIBUTE_TYPE_STRING,
            ],
            'triggered_by' => [
                'label'   => __('Triggered By'),
                'type'    => self::ATTRIBUTE_TYPE_ENUM,
                'options' => [
                    \Mirasvit\Helpdesk\Model\Config::CUSTOMER => __('Customer'),
                    \Mirasvit\Helpdesk\Model\Config::USER     => __('User'),
                    \Mirasvit\Helpdesk\Model\Config::THIRD    => __('Third Party'),
                    \Mirasvit\Helpdesk\Model\Config::RULE     => __('Rule')
                ]
            ]
        ];

        return $attributes;
    }
}