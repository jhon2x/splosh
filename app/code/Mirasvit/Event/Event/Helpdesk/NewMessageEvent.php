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



namespace Mirasvit\Event\Event\Helpdesk;

use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\Helpdesk\MessageData;

class NewMessageEvent extends ObservableEvent
{
    const IDENTIFIER = 'hdmx_message_new';

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        Context $context
    ) {
        parent::__construct($context);

        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            self::IDENTIFIER => __('Mirasvit Helpdesk / New message from customer'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(MessageData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        $message = $this->context->create('Mirasvit\Helpdesk\Model\Message')->load($params[MessageData::ID]);

        $params[MessageData::IDENTIFIER] = $message;

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        $params = $this->expand($params);

        /** @var MessageData $message */
        $message = $params[MessageData::IDENTIFIER];

        return __('Ticket ID: #%1 \nSubject: %2 \nURL: %3 \nDetails: "%4"',
            $params['code'],
            $params['subject'],
            $params['backend_url'],
            $message->getBodyPlain()
        );
    }

    /**
     * Plugin executing after a message is added to ticket.
     *
     * @param \Mirasvit\Helpdesk\Model\Ticket  $subject
     * @param \Mirasvit\Helpdesk\Model\Message $result
     *
     * @return \Mirasvit\Helpdesk\Model\Message
     */
    public function afterAddMessage(\Mirasvit\Helpdesk\Model\Ticket $subject, \Mirasvit\Helpdesk\Model\Message $result)
    {
        $params = [
            MessageData::ID             => $result->getId(),
            'code'                      => $subject->getCode(),
            'subject'                   => $subject->getSubject(),
            'backend_url'               => $subject->getBackendUrl(),
            self::PARAM_EXPIRE_AFTER    => 86400,
        ];

        $this->context->eventRepository->register(
            self::IDENTIFIER,
            [$result->getId()],
            $params
        );

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->moduleManager->isEnabled('Mirasvit_Helpdesk');
    }
}
