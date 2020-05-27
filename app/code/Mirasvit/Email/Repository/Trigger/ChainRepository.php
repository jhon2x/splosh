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



namespace Mirasvit\Email\Repository\Trigger;

use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Repository\QueueRepositoryInterface;
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Data\ChainInterfaceFactory;
use Mirasvit\Email\Model\ResourceModel\Trigger\Chain\CollectionFactory;

class ChainRepository implements ChainRepositoryInterface
{
    /**
     * @var ChainInterface[]
     */
    private $chainRegistry = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ChainInterfaceFactory
     */
    private $chainFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    public function __construct(
        QueueRepositoryInterface $queueRepository,
        EntityManager $entityManager,
        ChainInterfaceFactory $chainFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->queueRepository = $queueRepository;
        $this->entityManager = $entityManager;
        $this->chainFactory = $chainFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
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
        return $this->chainFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (isset($this->chainRegistry[$id])) {
            return $this->chainRegistry[$id];
        }

        $chain = $this->create();
        $chain = $this->entityManager->load($chain, $id);

        if ($chain->getId()) {
            $this->chainRegistry[$id] = $chain;
        }

        return $chain;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ChainInterface $model)
    {
        $model->setExcludeDays($model->getExcludeDays());

        return $this->entityManager->save($model);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ChainInterface $model)
    {
        $queueToDelete = $this->queueRepository->getCollection();
        $queueToDelete->addFieldToFilter(QueueInterface::STATUS, QueueInterface::STATUS_PENDING)
            ->addFieldToFilter(ChainInterface::ID, $model->getId());

        foreach ($queueToDelete as $queue) {
            $queue->cancel(__('Associated email chain was removed from a trigger.'));
        }

        return $this->entityManager->delete($model);
    }
}
