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



namespace Mirasvit\EmailReport\Plugin;

use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\EmailReport\Api\Repository\EmailRepositoryInterface;

class QueuePlugin
{
    private $emailRepository;

    public function __construct(
        EmailRepositoryInterface $emailRepository
    ) {
        $this->emailRepository = $emailRepository;
    }

    /**
     * @param QueueInterface $queue
     * @return QueueInterface
     */
    public function afterDelivery($queue)
    {
        $email = $this->emailRepository->create()
            ->setTriggerId($queue->getTriggerId())
            ->setQueueId($queue->getId());

        $this->emailRepository->ensure($email);

        return $queue;
    }
}
