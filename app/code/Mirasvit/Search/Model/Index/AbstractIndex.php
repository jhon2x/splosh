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



namespace Mirasvit\Search\Model\Index;

use Magento\Framework\DataObject;
use Mirasvit\Search\Api\Data\Index\DataMapperInterface;
use Mirasvit\Search\Api\Data\Index\InstanceInterface;
use Mirasvit\Search\Api\Data\IndexInterface;

use Mirasvit\Search\Model\Config;

/**
 * {@inheritdoc}
 *
 * @method $this setModel(IndexInterface $index)
 * @method IndexInterface getModel()
 */
abstract class AbstractIndex extends DataObject implements InstanceInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb[]
     */
    protected $searchCollection = [];

    /**
     * @var DataMapperInterface[]
     */
    private $dataMappers;

    public function __construct(
        Context $context,
        $dataMappers = []
    ) {
        $this->context = $context->getInstance();
        $this->context->getIndexer()->setIndex($this);
        $this->context->getSearcher()->setIndex($this);

        ksort($dataMappers);
        $this->dataMappers = $dataMappers;

        parent::__construct();
    }

    /**
     * @return Indexer
     */
    public function getIndexer()
    {
        return $this->context->getIndexer();
    }

    /**
     * @return string
     */
    public function getIndexName()
    {
        if ($this->getIdentifier() == 'catalogsearch_fulltext') {
            return $this->getIdentifier();
        }

        $identifier = $this->getIdentifier() . '_' . $this->getModel()->getId();

        return InstanceInterface::INDEX_PREFIX . $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataMappers($engine)
    {
        $mappers = [];

        foreach ($this->dataMappers as $key => $mapper) {
            if (strpos($key, 'engine-') === false) {
                // global mapper
                $mappers[$key] = $mapper;
            } elseif (strpos($key, "engine-$engine") !== false) {
                // engine based mapper
                $mappers[$key] = $mapper;
            }
        }

        return $mappers;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return __($this->getName())->getText();
    }

    /**
     * @return \Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse()
    {
        return $this->context->getSearcher()->getQueryResponse();
    }

    /**
     * Search collection
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    public function getSearchCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');

        /** @var \Magento\Store\Model\App\Emulation $emulation */
        $emulation = $objectManager->create('Magento\Store\Model\App\Emulation');

        $storeId = $this->getData('store_id') ? $this->getData('store_id') : 0;
        $storeIndex = $this->getIndexName() .'_'. mb_strtolower($this->getData('title')) .'_'. $storeId;

        if (!isset($this->searchCollection[$storeIndex][$storeId])) {
            $isEmulation = false;
            if ($storeId && $storeId != $storeManager->getStore()->getId()
            ) {
                $emulation->startEnvironmentEmulation($storeId);
                $isEmulation = true;
            }

            $this->searchCollection[$storeIndex][$storeId] = $this->buildSearchCollection();

            if ($isEmulation) {
                $this->searchCollection[$storeIndex][$storeId]->getSize();
                // get size before switch to default store
                $emulation->stopEnvironmentEmulation();
            }
        }

        return $this->searchCollection[$storeIndex][$storeId];
    }

    /**
     * Wights of attributes
     *
     * @return array
     */
    public function getAttributeWeights()
    {
        if ($this->getModel()) {
            return $this->getModel()->getAttributes();
        }

        return $this->getAttributes();
    }

    /**
     * Attribute id
     *
     * @param string $attributeCode
     * @return int
     */
    public function getAttributeId($attributeCode)
    {
        $attributes = array_keys($this->getAttributes());

        return array_search($attributeCode, $attributes);
    }

    /**
     * Reindex all stores
     *
     * @param int $storeId
     * @return bool
     */
    public function reindexAll($storeId = null)
    {
        $this->context->getIndexer()->reindexAll($storeId);

        $index = $this->getModel();
        $index->setStatus(IndexInterface::STATUS_READY);
        $index->save();

        // $this->context->getIndexRepository()->save($this->getModel());

        return true;
    }

    /**
     * Callback on model save after
     *
     * @return $this
     */
    public function afterModelSave()
    {
        return $this;
    }

    /**
     * Attribute code by id
     *
     * @param int $attributeId
     * @return string
     */
    public function getAttributeCode($attributeId)
    {
        $keys = array_keys($this->getAttributes());

        return isset($keys[$attributeId]) ? $keys[$attributeId] : 'option';
    }
}
