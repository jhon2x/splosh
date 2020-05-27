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



namespace Mirasvit\Email\Api\Service;

use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Event\Api\Data\EventInterface;

interface TriggerHandlerInterface
{
    /**
     * Handle events.
     *
     * @param TriggerInterface $trigger
     * @param array|null       $events
     *
     * @return $this
     */
    public function handleEvents(TriggerInterface $trigger, $events = null);

    /**
     * Handle separate event.
     *
     * @param TriggerInterface $trigger
     * @param EventInterface   $event
     *
     * @return $this
     */
    public function handleEvent(TriggerInterface $trigger, EventInterface $event);

    /**
     * Add new message to queue.
     *
     * @param TriggerInterface      $trigger
     * @param ChainInterface        $chain
     * @param EventInterface        $event
     *
     * @return bool|QueueInterface
     */
    public function enqueue(TriggerInterface $trigger, ChainInterface $chain, EventInterface $event);
}
