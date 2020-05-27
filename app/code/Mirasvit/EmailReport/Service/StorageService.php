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
 * @package   mirasvit/module-email-report
 * @version   2.0.8
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Service;

use Mirasvit\Email\Api\Repository\QueueRepositoryInterface;
use Mirasvit\EmailReport\Api\Service\StorageServiceInterface;
use Mirasvit\EmailReport\Api\Service\CookieInterface;

class StorageService implements StorageServiceInterface
{
    /**
     * @var CookieInterface
     */
    private $cookie;

    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    public function __construct(
        CookieInterface $cookie,
        QueueRepositoryInterface $queueRepository
    ) {
        $this->cookie = $cookie;
        $this->queueRepository = $queueRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function persistQueueHash($hash)
    {
        $this->cookie->set(self::QUEUE_PARAM_NAME, $hash, 3600 * 3);
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveQueueHash()
    {
        return $this->cookie->get(self::QUEUE_PARAM_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveQueue($hash = false)
    {
        if (!$hash) {
            $hash = $this->retrieveQueueHash();
        }

        if (!$hash) {
            return false;
        }

        $queue = $this->queueRepository->getByUniqueHash($hash);

        if ($queue && $queue->getTrigger() && $queue->getTrigger()->getId()) {
            return $queue;
        }

        return false;
    }
}
