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



namespace Mirasvit\Event\Event\AdminNotification;

use Magento\AdminNotification\Model\Inbox;
use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\CronEvent;
use Mirasvit\Event\EventData\InboxData;
use Magento\AdminNotification\Model\ResourceModel\Inbox\Collection;
use Magento\AdminNotification\Model\ResourceModel\Inbox\CollectionFactory as InboxCollectionFactory;

class NewEvent extends CronEvent
{
    const IDENTIFIER = 'admin_notification_new';

    /**
     * @var InboxCollectionFactory
     */
    private $inboxCollectionFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    public function __construct(
        InboxCollectionFactory $inboxCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        Context $context
    ) {
        parent::__construct($context);

        $this->inboxCollectionFactory = $inboxCollectionFactory;
        $this->localeDate = $localeDate;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [self::IDENTIFIER => __('Admin Notification / New Notification')];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(InboxData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        $params = $this->expand($params);

        /** @var InboxData $inbox */
        $inbox = $params[InboxData::IDENTIFIER];

        return __("%1\nSeverity: %2\n\n%3\n%4\n5",
            $inbox->getTitle(),
            $inbox->getSeverities($inbox->getSeverity()),
            $inbox->getDescription(),
            $inbox->getUrl(),
            $this->formatNotificationDate($inbox->getDateAdded())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        $inbox = $this->context->create(InboxData::class)->load($params[InboxData::ID]);

        $params[InboxData::IDENTIFIER] = $inbox;

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $lastCheck = $this->context->timeService->getFlagDateTime(self::IDENTIFIER);

        /** @var Collection $inboxCollection */
        $inboxCollection = $this->inboxCollectionFactory->create();
        $inboxCollection->addFieldToFilter(InboxData::DATE_ADDED, ['gteq' => $lastCheck]);

        /** @var Inbox $inbox */
        foreach ($inboxCollection as $inbox) {
            $params = [InboxData::ID => $inbox->getId()];

            $this->context->eventRepository->register(
                self::IDENTIFIER,
                [$params[InboxData::ID]],
                $params
            );
        }

        $this->context->timeService->setFlagTimestamp(self::IDENTIFIER);
    }

    /**
     * Format notification date (show only time if notification has been added today)
     *
     * @param string $dateString
     * @return string
     */
    public function formatNotificationDate($dateString)
    {
        $date = new \DateTime($dateString);
        if ($date == new \DateTime('today')) {
            return $this->localeDate->formatDateTime(
                $date,
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::SHORT
            );
        }
        return $this->localeDate->formatDateTime(
            $date,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
    }
}
