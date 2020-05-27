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



namespace Mirasvit\Event\Event;

use Mirasvit\Event\Api\Data\Event\CronEventInterface;
use Mirasvit\Event\Api\Data\EventInterface;

abstract class CronEvent implements CronEventInterface
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function check($eventIdentifier, $ruleConditions)
    {
        $this->execute();

        $result = [];

        $events = $this->context->eventRepository->getCollection();
        $events->addFieldToFilter(EventInterface::IDENTIFIER, $eventIdentifier)
            ->addFieldToFilter(EventInterface::ID, ['gt' => $this->context->flagService->get($eventIdentifier)]);

        foreach ($events as $event) {
            $data = $this->expand($event->getParams());

            if ($this->context->validatorService->validate($ruleConditions, $data)) {
                $result[] = $event;
            }

            $this->context->flagService->set($eventIdentifier, $event->getId());
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return true;
    }
}
