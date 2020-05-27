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



namespace Mirasvit\Mq\Provider\Mysql;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\Mq\Api\Data\EnvelopeInterface;
use Mirasvit\Mq\Api\Data\EnvelopeInterfaceFactory;
use Mirasvit\Mq\Provider\Mysql\Api\Data\QueueInterface;
use Mirasvit\Mq\Provider\Mysql\Repository\QueueRepository;
use Magento\Framework\Message\ManagerInterface;

class Queue implements \Mirasvit\Mq\Api\QueueInterface
{
    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var EnvelopeInterfaceFactory
     */
    private $envelopeFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    public function __construct(
        QueueRepository $queueRepository,
        EnvelopeInterfaceFactory $envelopeFactory,
        ResourceConnection $resourceConnection,
        ManagerInterface $messageManager
    ) {
        $this->queueRepository    = $queueRepository;
        $this->envelopeFactory    = $envelopeFactory;
        $this->resourceConnection = $resourceConnection;
        $this->messageManager     = $messageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(EnvelopeInterface $envelope)
    {
        if (!$this->resourceConnection->getConnection()->isTableExists(
            $this->resourceConnection->getTableName(QueueInterface::TABLE_NAME)
        )) {
            return false;
        }

        $queue = $this->queueRepository->create();
        $queue->setBody($envelope->getBody())
            ->setQueueName($envelope->getQueueName())
            ->setStatus(QueueInterface::STATUS_NEW);

        $this->queueRepository->save($queue);
    }

    /**
     * {@inheritdoc}
     */
    public function peek()
    {
        $collection = $this->queueRepository->getCollection();
        $collection->addFieldToFilter(QueueInterface::STATUS, QueueInterface::STATUS_NEW)
            ->setPageSize(1);

        /** @var QueueInterface $message */
        $message = $collection->fetchItem();

        if (!$message) {
            return false;
        }

        $envelope = $this->envelopeFactory->create();
        $envelope->setReference($message->getId())
            ->setQueueName($message->getQueueName())
            ->setBody($message->getBody());

        return $envelope;
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledge(EnvelopeInterface $envelope)
    {
        $message = $this->queueRepository->get($envelope->getReference());

        if (!$message) {
            return;
        }

        $message->setStatus(QueueInterface::STATUS_COMPLETE);
        $this->queueRepository->save($message);
    }

    /**
     * {@inheritdoc}
     */
    public function reject(EnvelopeInterface $envelope, $requeue = false)
    {
        $message = $this->queueRepository->get($envelope->getReference());

        if (!$message) {
            return;
        }

        if ($requeue && $message->getRetries() < 5) {
            $message->setStatus(QueueInterface::STATUS_NEW);
            $message->setRetries($message->getRetries() + 1);
        } else {
            $message->setStatus(QueueInterface::STATUS_COMPLETE);
        }

        $this->queueRepository->save($message);
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe($callback, $maxExecutionTime)
    {
        $ts = microtime(true);

        while (true) {
            while ($envelope = $this->peek()) {
                try {
                    call_user_func($callback, $envelope);
                    $this->acknowledge($envelope);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $this->reject($envelope, true);
                }

                if (microtime(true) - $ts > $maxExecutionTime) {
                    break;
                }
            }

            sleep(1);

            if (microtime(true) - $ts > $maxExecutionTime) {
                break;
            }
        }
    }
}