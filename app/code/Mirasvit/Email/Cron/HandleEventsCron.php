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



namespace Mirasvit\Email\Cron;

use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Email\Model\Trigger\Handler as TriggerHandler;
use Mirasvit\Event\Api\Data\Event\CronEventInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class HandleEventsCron
{
    /**
     * @var TriggerRepositoryInterface
     */
    protected $triggerRepository;

    /**
     * @var TriggerHandler
     */
    protected $triggerHandler;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        TriggerRepositoryInterface $triggerRepository,
        TriggerHandler $triggerHandler
    ) {
        $this->triggerRepository= $triggerRepository;
        $this->triggerHandler = $triggerHandler;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Fetch new events and queue emails.
     *
     * @return void
     */
    public function execute()
    {
        $triggers = $this->triggerRepository->getCollection();
        $triggers->addActiveFilter();

        foreach ($triggers as $trigger) {
            foreach ($trigger->getEvents() as $eventCode) {
                $event = $this->eventRepository->getInstance($eventCode);

                if ($event && $event instanceof CronEventInterface) {
                    // @todo convert fatal errors to exceptions and handle them here to avoid cron schedule hanging

                    // we should validate only triggering events
                    if (in_array($eventCode, $trigger->getTriggeringEvents())) {
                        $event->check($eventCode, $trigger->getRule());
                    } else {
                        $event->check($eventCode, []);
                    }
                }
            }
        }
    }
}
