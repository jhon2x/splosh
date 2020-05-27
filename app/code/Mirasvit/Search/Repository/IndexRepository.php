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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Mirasvit\Search\Api\Data\Index\InstanceInterface;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\Search\Api\Data\IndexInterfaceFactory;
use Mirasvit\Search\Model\ResourceModel\Index\CollectionFactory as IndexCollectionFactory;
use Magento\Framework\ObjectManagerInterface;

class IndexRepository implements IndexRepositoryInterface
{
    /**
     * @var array
     */
    private static $indexCache = [];

    /**
     * @var array
     */
    private static $instanceCache = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var IndexInterfaceFactory
     */
    private $indexFactory;

    /**
     * @var IndexCollectionFactory
     */
    private $indexCollectionFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string[]
     */
    private $indicesPool = [];

    public function __construct(
        EntityManager $entityManager,
        IndexInterfaceFactory $indexFactory,
        IndexCollectionFactory $indexCollectionFactory,
        ObjectManagerInterface $objectManager,
        $indices = []
    ) {
        $this->entityManager = $entityManager;
        $this->indexFactory = $indexFactory;
        $this->indexCollectionFactory = $indexCollectionFactory;
        $this->objectManager = $objectManager;
        $this->indicesPool = $indices;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->indexCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->indexFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $id = str_replace(InstanceInterface::INDEX_PREFIX, '', $id);

        if (!array_key_exists($id, self::$indexCache)) {
            $index = $this->create();

            if (is_numeric($id)) {
                $this->entityManager->load($index, $id);
            } else {
                /** @var \Mirasvit\Search\Model\Index $index */
                $index = $index->load($id, IndexInterface::IDENTIFIER);
            }

            self::$indexCache[$id] = $index;
        }

        return self::$indexCache[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(IndexInterface $index)
    {
        $this->entityManager->delete($index);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function save(IndexInterface $index)
    {
        /** @var \Mirasvit\Search\Model\Index $index */

        $reindexRequired = false;

        if ($index->dataHasChangedFor(IndexInterface::ATTRIBUTES_SERIALIZED)) {
            $reindexRequired = true;
        }

        if ($index->dataHasChangedFor(IndexInterface::PROPERTIES_SERIALIZED)) {
            $reindexRequired = true;
        }

        if ($reindexRequired) {
            $index->setStatus(IndexInterface::STATUS_INVALID);
        }

        $this->entityManager->save($index);

        return $index;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($index)
    {
        if (is_object($index)) {
            $identifier = $index->getIdentifier();

            $instance = $this->getInstanceByIdentifier($identifier);

            if (!$instance) {
                throw new \Exception(__("Instance for '%1' not found", $identifier));
            }

            $instance
                ->setData($index->getData())
                ->setModel($index);

            return $instance;
        } else {
            $index = str_replace(InstanceInterface::INDEX_PREFIX, '', $index);

            $instance = $this->getInstanceByIdentifier($index);
            $model = $this->get($index);

            $instance
                ->setData($model->getData())
                ->setModel($model);

            return $instance;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $result = [];

        foreach ($this->indicesPool as $class) {
            $result[] = $this->objectManager->get($class);
        }

        return $result;
    }

    /**
     * @param string $identifier
     * @return InstanceInterface|false
     */
    private function getInstanceByIdentifier($identifier)
    {
        if (!array_key_exists($identifier, self::$instanceCache)) {
            self::$instanceCache[$identifier] = false;

            foreach ($this->indicesPool as $class) {
                if ($this->objectManager->get($class)->getIdentifier() == $identifier) {
                    self::$instanceCache[$identifier] = $this->objectManager->create($class);
                }
            }
        }

        return self::$instanceCache[$identifier];
    }
}
