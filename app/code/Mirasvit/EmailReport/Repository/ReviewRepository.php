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
use Mirasvit\EmailReport\Api\Data\ReviewInterface;
use Mirasvit\EmailReport\Api\Data\ReviewInterfaceFactory;
use Mirasvit\EmailReport\Model\ResourceModel\Review\CollectionFactory;
use Mirasvit\EmailReport\Api\Repository\ReviewRepositoryInterface;
use Magento\Framework\Stdlib\DateTime;

class ReviewRepository implements ReviewRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ReviewInterfaceFactory
     */
    private $factory;

    public function __construct(
        ReviewInterfaceFactory $factory,
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
        $review = $this->factory->create();
        $review = $this->entityManager->load($review, $id);

        return $review->getId() ? $review : false;
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
    public function save(ReviewInterface $review)
    {
        $review->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        return $this->entityManager->save($review);
    }

    /**
     * {@inheritDoc}
     */
    public function ensure(ReviewInterface $review)
    {
        $size = $this->getCollection()
            ->addFieldToFilter(ReviewInterface::QUEUE_ID, $review->getQueueId())
            ->addFieldToFilter(ReviewInterface::PARENT_ID, $review->getParentId())
            ->getSize();

        if (!$size) {
            $this->save($review);
        }

        return $review;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(ReviewInterface $review)
    {
        return $this->entityManager->delete($review);
    }
}
