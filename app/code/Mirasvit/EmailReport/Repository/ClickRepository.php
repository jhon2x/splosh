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
use Mirasvit\EmailReport\Api\Data\ClickInterface;
use Mirasvit\EmailReport\Api\Data\ClickInterfaceFactory;
use Mirasvit\EmailReport\Model\ResourceModel\Click\CollectionFactory;
use Mirasvit\EmailReport\Api\Repository\ClickRepositoryInterface;
use Magento\Framework\Stdlib\DateTime;

class ClickRepository implements ClickRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ClickInterfaceFactory
     */
    private $factory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        ClickInterfaceFactory $factory,
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
        $click = $this->factory->create();
        $click = $this->entityManager->load($click, $id);

        return $click->getId() ? $click : false;
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
    public function save(ClickInterface $click)
    {

        $click->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        return $this->entityManager->save($click);
    }

    /**
     * {@inheritDoc}
     */
    public function ensure(ClickInterface $click)
    {
        $size = $this->getCollection()
            ->addFieldToFilter(ClickInterface::QUEUE_ID, $click->getQueueId())
            ->getSize();

        if (!$size) {
            $this->save($click);
        }

        return $click;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(ClickInterface $click)
    {
        return $this->entityManager->delete($click);
    }
}
