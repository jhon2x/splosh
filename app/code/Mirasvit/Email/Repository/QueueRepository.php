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



namespace Mirasvit\Email\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\Email\Api\Repository\QueueRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Data\QueueInterfaceFactory;
use Mirasvit\Email\Model\ResourceModel\Queue\CollectionFactory;
use Mirasvit\Email\Helper\Serializer;

class QueueRepository implements QueueRepositoryInterface
{
    /**
     * @var QueueInterface[]
     */
    private $queueRegistry = [];

    /**
     * @var QueueInterface[]
     */
    private $queueByHashRegistry = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * QueueRepository constructor.
     *
     * @param EntityManager         $entityManager
     * @param QueueInterfaceFactory $queueFactory
     * @param CollectionFactory     $collectionFactory
     * @param Serializer            $serializer
     */
    public function __construct(
        EntityManager         $entityManager,
        QueueInterfaceFactory $queueFactory,
        CollectionFactory     $collectionFactory,
        Serializer            $serializer
    ) {
        $this->queueFactory      = $queueFactory;
        $this->collectionFactory = $collectionFactory;
        $this->entityManager     = $entityManager;
        $this->serializer        = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if (isset($this->queueRegistry[$id])) {
            return $this->queueRegistry[$id];
        }

        $queue = $this->create();
        $queue = $this->entityManager->load($queue, $id);
        //$queue = $queue->load($id);

        if ($queue->getId()) {
            $this->queueRegistry[$id] = $queue;
        } else {
            return false;
        }

        return $queue;
    }

    /**
     * {@inheritDoc}
     */
    public function getByUniqueHash($uniqueHash)
    {
        if (isset($this->queueByHashRegistry[$uniqueHash])) {
            return $this->queueByHashRegistry[$uniqueHash];
        }

        /** @var $queue QueueInterface */
        $queue = $this->getCollection()
            ->addFieldToFilter(QueueInterface::UNIQUE_HASH, $uniqueHash)
            ->getFirstItem();

        if ($queue->getId()) {
            $queue = $this->get($queue->getId());
        }

        $this->queueByHashRegistry[$uniqueHash] = $queue;

        return $queue;
    }

    /**
     * {@inheritDoc}
     */
    public function save(QueueInterface $queue)
    {
        if ($queue->hasData(QueueInterface::ARGS)) {
            $queue->setArgsSerialized($this->serializer->serialize($queue->getData(QueueInterface::ARGS)));
        }

        if (!$queue->hasData(QueueInterface::STATUS)) {
            $queue->setStatus(QueueInterface::STATUS_PENDING)
                ->setData(QueueInterface::STATUS_MESSAGE, __('Default status'));
        }

        $this->prepareHistory($queue);
        
        if (!$queue->getUniqHash()) {
            $queue->setUniqHash(hash('sha256', $queue->getUniqKey()));
        }

        return $this->entityManager->save($queue);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(QueueInterface $queue)
    {
        return $this->entityManager->delete($queue);
    }

    /**
     * {@inheritDoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->queueFactory->create();
    }

    /**
     * Prepare history string.
     *
     * @param QueueInterface $queue
     */
    private function prepareHistory(QueueInterface $queue)
    {
        if ($queue->getOrigData(QueueInterface::STATUS) != $queue->getData(QueueInterface::STATUS)) {
            $newStatus = $queue->getData(QueueInterface::STATUS);

            $historyMessage = __("%1 - status changed to '%2'", date('M d, Y H:i:s'), $newStatus);
            if ($queue->hasData(QueueInterface::STATUS_MESSAGE)) {
                $historyMessage .= ' [' . $queue->getData(QueueInterface::STATUS_MESSAGE) . ']';
            }

            $history = $queue->getData(QueueInterface::HISTORY);

            $history .= $historyMessage . PHP_EOL;

            $queue->setData(QueueInterface::HISTORY, $history);
        }
    }
}
