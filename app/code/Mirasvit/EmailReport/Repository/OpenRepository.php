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
use Mirasvit\EmailReport\Api\Data\OpenInterface;
use Mirasvit\EmailReport\Api\Data\OpenInterfaceFactory;
use Mirasvit\EmailReport\Model\ResourceModel\Open\CollectionFactory;
use Mirasvit\EmailReport\Api\Repository\OpenRepositoryInterface;
use Magento\Framework\Stdlib\DateTime;

class OpenRepository implements OpenRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var OpenInterfaceFactory
     */
    private $factory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        OpenInterfaceFactory $factory,
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
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->factory->create();
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        $open = $this->factory->create();
        $open = $this->entityManager->load($open, $id);

        return $open->getId() ? $open : false;
    }

    /**
     * {@inheritDoc}
     */
    public function save(OpenInterface $open)
    {
        $open->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        return $this->entityManager->save($open);
    }

    /**
     * {@inheritDoc}
     */
    public function ensure(OpenInterface $open)
    {
        $size = $this->getCollection()
            ->addFieldToFilter(OpenInterface::QUEUE_ID, $open->getQueueId())
            ->getSize();

        if (!$size) {
            $this->save($open);
        }

        return $open;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(OpenInterface $open)
    {
        return $this->entityManager->delete($open);
    }
}
