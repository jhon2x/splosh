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
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Model\ResourceModel\Trigger\Chain\CollectionFactory as ChainCollectionFactory;
use Mirasvit\Email\Model\Trigger\ChainFactory;

class Trigger extends AbstractDb
{
    /**
     * @var ChainFactory
     */
    protected $chainFactory;

    /**
     * @var ChainCollectionFactory
     */
    protected $chainCollectionFactory;

    /**
     * Trigger constructor.
     *
     * @param ChainFactory           $chainFactory
     * @param ChainCollectionFactory $chainCollectionFactory
     * @param Context                $context
     */
    public function __construct(
        ChainFactory $chainFactory,
        ChainCollectionFactory $chainCollectionFactory,
        Context $context
    ) {
        $this->chainFactory = $chainFactory;
        $this->chainCollectionFactory = $chainCollectionFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(TriggerInterface::TABLE_NAME, TriggerInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCreatedAt()) {
            $object->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        }

        if (is_array($object->getData('store_ids'))) {
            $object->setData('store_ids', implode(',', $object->getData('store_ids')));
        }

        if (is_array($object->getData('cancellation_event'))) {
            $object->setData('cancellation_event', implode(',', $object->getData('cancellation_event')));
        }

        $object->setUpdatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        if ($object->hasData('rule')) {
            $this->saveRule($object);
        }

        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(AbstractModel $object)
    {
        if (!$object->getIsMassAction()) { // Save chain only if a trigger saved from a trigger edit page
            $this->saveChain($object);
        }
    }

    /**
     * @param AbstractModel|TriggerInterface $object
     * {@inheritdoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getStoreIds()) {
            $object->setStoreIds($object->getStoreIds());
        } else {
            $object->setStoreIds([0]);
        }

        $object->setCancellationEvent($object->getCancellationEvent());

        return parent::_afterLoad($object);
    }

    /**
     * Save chain for trigger
     *
     * @param \Mirasvit\Email\Model\Trigger $object
     * @return $this
     *  @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveChain($object)
    {
        $collectionToDelete = $this->chainCollectionFactory->create()
            ->addFieldToFilter('trigger_id', $object->getId())
            ->addFieldToFilter(
                'chain_id',
                ($object->hasData('chain')) ? ['nin' => array_keys($object->getChain())] : ['like' => '%']
            );

        foreach ($collectionToDelete as $item) {
            $item->delete();
        }

        if ($object->hasData('chain')) {
            foreach ($object->getChain() as $chainId => $chainData) {
                $chain = $this->chainFactory->create()->load($chainId);
                $chain->addData($chainData)
                    ->setTriggerId($object->getId())
                    ->save();
            }
        }

        return $this;
    }

    /**
     * Save rule for trigger
     *
     * @param \Mirasvit\Email\Model\Trigger $object
     * @return $this
     */
    protected function saveRule($object)
    {
        if ($object->getData('rule') && is_array($object->getData('rule'))) {
            $rule = $object->getData('rule');

            $model = $object->getRule();
            $model->setIsActive(1)
                ->setIsSystem(1)
                ->loadPost($rule)
                ->setTitle('Run Rule')
                ->save();

            $object->setRunRuleId($model->getId());
        }

        return $this;
    }
}
