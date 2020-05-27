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
 * @package   mirasvit/module-message-queue
 * @version   1.0.9
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Mq\Api;

use Mirasvit\Mq\Api\Data\EnvelopeInterface;

interface QueueInterface
{
    /**
     * @return bool
     */
    public function isAvailable();

    /**
     * Add message to queue
     *
     * @param EnvelopeInterface $envelope
     * @return void|bool
     */
    public function enqueue(EnvelopeInterface $envelope);

    /**
     * Get next message from queue
     *
     * @return EnvelopeInterface|false
     */
    public function peek();

    /**
     * Mark message as processed
     *
     * @param EnvelopeInterface $envelope
     * @return void
     */
    public function acknowledge(EnvelopeInterface $envelope);

    /**
     * Reject message
     *
     * @param EnvelopeInterface $envelope
     * @param bool $requeue
     * @return bool
     */
    public function reject(EnvelopeInterface $envelope, $requeue = false);

    /**
     * Subscribe to queue
     *
     * @param array|callable $callback
     * @param int $maxExecutionTime
     * @return void
     */
    public function subscribe($callback, $maxExecutionTime);
}