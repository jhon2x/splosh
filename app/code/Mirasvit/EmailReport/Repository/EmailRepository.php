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
use Mirasvit\EmailReport\Api\Data\EmailInterface;
use Mirasvit\EmailReport\Api\Data\EmailInterfaceFactory;
use Mirasvit\EmailReport\Model\ResourceModel\Email\CollectionFactory;
use Mirasvit\EmailReport\Api\Repository\EmailRepositoryInterface;
use Magento\Framework\Stdlib\DateTime;

class EmailRepository implements EmailRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EmailInterfaceFactory
     */
    private $factory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        EmailInterfaceFactory $factory,
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
        $email = $this->create();
        $email = $this->entityManager->load($email, $id);

        return $email->getId() ? $email : false;
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
    public function save(EmailInterface $email)
    {
        $email->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        return $this->entityManager->save($email);
    }

    /**
     * {@inheritDoc}
     */
    public function ensure(EmailInterface $email)
    {
        $size = $this->getCollection()
            ->addFieldToFilter(EmailInterface::QUEUE_ID, $email->getQueueId())
            ->getSize();

        if (!$size) {
            $this->save($email);
        }

        return $email;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(EmailInterface $email)
    {
        return $this->entityManager->delete($email);
    }
}
