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
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Email\Api\Data\TriggerInterfaceFactory;
use Mirasvit\Email\Model\ResourceModel\Trigger\CollectionFactory;

class TriggerRepository implements TriggerRepositoryInterface
{
    /**
     * @var TriggerInterface[]
     */
    private $triggerRegistry = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TriggerInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        EntityManager $entityManager,
        TriggerInterfaceFactory $modelFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->entityManager = $entityManager;
        $this->modelFactory = $modelFactory;
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
        return $this->modelFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (isset($this->triggerRegistry[$id])) {
            return $this->triggerRegistry[$id];
        }

        $trigger = $this->create();
        $trigger = $this->entityManager->load($trigger, $id);

        if ($trigger->getId()) {
            $this->triggerRegistry[$id] = $trigger;
        } else {
            return false;
        }

        return $trigger;
    }

    /**
     * {@inheritdoc}
     */
    public function save(TriggerInterface $model)
    {
        $model->setRuleSerialized(\Zend_Json_Encoder::encode($model->getRule() ? $model->getRule() : []));
        $model->setStoreIds($model->getStoreIds());
        $model->setCancellationEvent($model->getCancellationEvent());
        $model->setAdminEmail($model->getAdminEmail());

        $this->entityManager->save($model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(TriggerInterface $model)
    {
        return $this->entityManager->delete($model);
    }
}
