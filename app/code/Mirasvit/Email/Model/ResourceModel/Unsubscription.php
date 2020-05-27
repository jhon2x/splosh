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



namespace Mirasvit\Email\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;

class Unsubscription extends AbstractDb
{
    /**
     * @var QueueCollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * Constructor
     *
     * @param QueueCollectionFactory $queueCollectionFactory
     * @param Context                $context
     */
    public function __construct(
        QueueCollectionFactory $queueCollectionFactory,
        Context $context
    ) {
        $this->queueCollectionFactory = $queueCollectionFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('mst_email_unsubscription', 'unsubscription_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCreatedAt()) {
            $object->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        }

        $object->setUpdatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(AbstractModel $object)
    {
        $queueCollection = $this->queueCollectionFactory->create()
            ->addFieldToFilter(QueueInterface::RECIPIENT_EMAIL, $object->getEmail())
            ->addFieldToFilter(QueueInterface::STATUS, QueueInterface::STATUS_PENDING);

        if ($object->getTriggerId() > 0) {
            $queueCollection->addFieldToFilter(TriggerInterface::ID, $object->getTriggerId());
        }

        foreach ($queueCollection as $item) {
            $item->unsubscribe();
        }

        return parent::_afterSave($object);
    }
}
