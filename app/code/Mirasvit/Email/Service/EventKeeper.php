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



namespace Mirasvit\Email\Service;

use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Event\Api\Service\EventKeeperInterface;

class EventKeeper implements EventKeeperInterface
{
    /**
     * @var TriggerRepositoryInterface
     */
    private $triggerRepository;

    /**
     * EventKeeper constructor.
     *
     * @param TriggerRepositoryInterface $triggerRepository
     */
    public function __construct(TriggerRepositoryInterface $triggerRepository)
    {
        $this->triggerRepository = $triggerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        $events = [];
        /** @var TriggerInterface $trigger */
        foreach ($this->triggerRepository->getCollection()->addActiveFilter() as $trigger) {
            $events = array_merge($events, $trigger->getEvents());
        }

        return $events;
    }
}
