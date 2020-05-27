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



namespace Mirasvit\EmailReport\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\EmailReport\Api\Data\OrderInterface;
use Mirasvit\EmailReport\Api\Data\OrderInterfaceFactory;
use Mirasvit\EmailReport\Model\ResourceModel\Order\CollectionFactory;
use Mirasvit\EmailReport\Api\Repository\OrderRepositoryInterface;
use Magento\Framework\Stdlib\DateTime;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var OrderInterfaceFactory
     */
    private $factory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        OrderInterfaceFactory $factory,
        CollectionFactory $collectionFactory,
        EntityManager $entityManager
    ) {
        $this->factory = $factory;
        $this->collectionFactory = $collectionFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        /** @var OrderInterface $order */
        $order = $this->factory->create();
        $order = $this->entityManager->load($order, $id);

        return $order->getId() ? $order : false;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->factory->create();
    }

    /**
     * {@inheritDoc}
     */
    public function save(OrderInterface $order)
    {
        $order->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        return $this->entityManager->save($order);
    }

    /**
     * {@inheritDoc}
     */
    public function ensure(OrderInterface $order)
    {
        $size = $this->getCollection()
            ->addFieldToFilter(OrderInterface::QUEUE_ID, $order->getQueueId())
            ->addFieldToFilter(OrderInterface::PARENT_ID, $order->getParentId())
            ->getSize();

        if (!$size) {
            $this->save($order);
        }

        return $order;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(OrderInterface $order)
    {
        return $this->entityManager->delete($order);
    }
}
