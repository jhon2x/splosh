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



namespace Mirasvit\Mq\Provider\Mysql\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\Mq\Provider\Mysql\Api\Data\QueueInterface;
use Mirasvit\Mq\Provider\Mysql\Model\QueueFactory;
use Mirasvit\Mq\Provider\Mysql\Model\ResourceModel\Queue\CollectionFactory;

class QueueRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var QueueFactory
     */
    private $queueFactory;

    /**
     * @var CollectionFactory
     */
    private $queueCollectionFactory;

    public function __construct(
        EntityManager $entityManager,
        QueueFactory $queueFactory,
        CollectionFactory $queueCollectionFactory
    ) {
        $this->entityManager = $entityManager;
        $this->queueFactory = $queueFactory;
        $this->queueCollectionFactory = $queueCollectionFactory;
    }

    /**
     * @return QueueInterface[]|\Mirasvit\Mq\Provider\Mysql\Model\ResourceModel\Queue\Collection
     */
    public function getCollection()
    {
        return $this->queueCollectionFactory->create();
    }

    /**
     * @return QueueInterface
     */
    public function create()
    {
        return $this->queueFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $queue = $this->create();
        $this->entityManager->load($queue, $id);

        return $queue->getId() ? $queue : false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(QueueInterface $queue)
    {
        $this->entityManager->delete($queue);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(QueueInterface $queue)
    {
        return $this->entityManager->save($queue);
    }
}