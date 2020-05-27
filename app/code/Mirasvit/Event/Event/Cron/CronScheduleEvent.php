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



namespace Mirasvit\Event\Event\Cron;

use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\ValidatableEvent;
use Mirasvit\Event\EventData\ScheduleData;

class CronScheduleEvent extends ValidatableEvent
{
    const IDENTIFIER = 'cron_schedule';

    private $scheduleCollectionFactory;


    public function __construct(
        ScheduleCollectionFactory $scheduleCollectionFactory,
        Context $context
    ) {
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            self::IDENTIFIER => __('Cron / Cron job'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(ScheduleData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        /** @var ScheduleData $schedule */
        $schedule = $this->context->create(ScheduleData::class)->load($params[ScheduleData::ID]);

        $params[ScheduleData::IDENTIFIER] = $schedule;

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        $params = $this->expand($params);

        /** @var ScheduleData $schedule */
        $schedule = $params[ScheduleData::IDENTIFIER];

        return __(
            "Cron job #%1 [%2]: %3",
            $schedule->getId(),
            $schedule->getJobCode(),
            $schedule->getStatus()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function check($eventIdentifier, $ruleConditions)
    {
        $events = [];

        $collection = $this->scheduleCollectionFactory->create();

        foreach ($collection as $schedule) {

            /** @var ScheduleData $scheduleData */
            $scheduleData = $this->context->create(ScheduleData::class);
            $scheduleData->load($schedule->getId());

            $isValid = $this->context->validatorService->validate($ruleConditions, [
                $scheduleData->getIdentifier() => $scheduleData,
            ]);

            if (!$isValid) {
                continue;
            }

            $params = [
                ScheduleData::ID => $schedule->getId(),
            ];

            $event = $this->context->eventRepository->register(
                self::IDENTIFIER,
                [$schedule->getId()],
                $params
            );

            if ($event) {
                $events[] = $event;
            }
        }

        return $events;
    }
}