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



namespace Mirasvit\Search\Index\Magento\Catalog\Product;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Config as EavConfig;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;
use Magento\Catalog\Model\Product\ProductList\Toolbar;
use Magento\Framework\Search\Request\Dimension;
use Mirasvit\Core\Service\CompatibilityService;

class Index extends AbstractIndex
{
    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $attributeToCode;

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var LayerResolver
     */
    private $layerResolver;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    public function __construct(
        AttributeCollectionFactory $attributeCollectionFactory,
        LayerResolver $layerResolver,
        EavConfig $eavConfig,
        Context $context,
        $dataMappers
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->layerResolver = $layerResolver;
        $this->eavConfig = $eavConfig;

        parent::__construct($context, $dataMappers);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Magento / Product';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'catalogsearch_fulltext';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($extended = false)
    {
        if (!$this->attributes) {
            $collection = $this->attributeCollectionFactory->create()
                ->addVisibleFilter()
                ->setOrder('attribute_id', 'asc');

            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            foreach ($collection as $attribute) {
                $allLockedFields = $this->eavConfig->get(
                    $attribute->getEntityType()->getEntityTypeCode() . '/attributes/' . $attribute->getAttributeCode()
                );
                if (!is_array($allLockedFields)) {
                    $allLockedFields = [];
                }

                if ($attribute->getDefaultFrontendLabel() && !isset($allLockedFields['is_searchable'])) {
                    $this->attributes[$attribute->getAttributeCode()] = $attribute->getDefaultFrontendLabel();
                }
            }
        }

        $result = $this->attributes;

        if ($extended) {
            $result['visibility'] = '';
            $result['options'] = '';
            $result['category_ids'] = '';
            $result['status'] = '';
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCode($attributeId)
    {
        if (!isset($this->attributeToCode[$attributeId])) {
            $attribute = $this->attributeCollectionFactory->create()
                ->getItemByColumnValue('attribute_id', $attributeId);

            $this->attributeToCode[$attributeId] = $attribute['attribute_code'];
        }

        return $this->attributeToCode[$attributeId];
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeWeights()
    {
        $result = [];
        $collection = $this->attributeCollectionFactory->create()
            ->addVisibleFilter()
            ->setOrder('search_weight', 'desc');

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($collection as $attribute) {
            if ($attribute->getIsSearchable()) {
                $result[$attribute->getAttributeCode()] = $attribute->getSearchWeight();
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'entity_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        /** @var \Magento\Catalog\Model\Layer\Search $layer */
        $layer = $this->layerResolver->get();

        if ($this->context->getConfig()->isMultiStoreModeEnabled()) {
            $originalCategory = $layer->getData('current_category');
            // set random category for multi-store mode
            // this mode can be not compatible with some layered navigation
            $category = $this->context->getObjectManager()->create('Magento\Catalog\Model\Category');
            $category->setId(rand(100000, 900000));
            $layer->setData('current_category', $category);

            $collection = $layer->getProductCollection();

            $layer->setData('current_category', $originalCategory);
        } else {
            $collection = $layer->getProductCollection();
        }

        if ($this->getModel()->getProperty('out_of_stock_to_end')) {
            $this->sortByStockStatus($collection);
        }

        if (!$this->context->getRequest()->getParam(Toolbar::ORDER_PARAM_NAME)) {
            $field = 'relevance';
            $direction = 'desc';

            if ($this->getModel()->getProperty('order')) {
                list($field, $direction) = explode('/', $this->getModel()->getProperty('order'));
            }

            $collection->setOrder($field, $direction);
        }

        return $collection;
    }

    /**
     * Join product stock status to collection of products.
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     */
    private function sortByStockStatus(\Magento\Eav\Model\Entity\Collection\AbstractCollection $collection)
    {
        $join = true;
        $alias = 'cataloginventory_stock_status_index';
        $from = $collection->getSelect()->getPart('from');
        if (array_key_exists('stock_status_index', $from) && !CompatibilityService::is23()) {
            $join = false;
            $alias = 'stock_status_index';
        }

        if ($join) {
            /** @var \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration */
            $stockConfiguration = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Api\StockConfigurationInterface');
            $websiteId = $stockConfiguration->getDefaultScopeId();
            $joinCondition = $collection->getConnection()->quoteInto(
                'e.entity_id = ' . $alias . '.product_id' . ' AND ' . $alias . '.website_id = ?',
                $websiteId
            );

            $joinCondition .= $collection->getConnection()->quoteInto(
                ' AND ' . $alias . '.stock_id = ?',
                \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID
            );
            $collection->getSelect()->joinLeft(
                [$alias => $collection->getResource()->getTable('cataloginventory_stock_status')],
                $joinCondition,
                ['is_salable' => 'stock_status']
            );
        }

        $collection->getSelect()->order(new \Zend_Db_Expr($alias . '.stock_status DESC'));

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function reindexAll($storeId = null)
    {
        $configData = [
            'fieldsets'  => [],
            'indexer_id' => 'catalogsearch_fulltext',
            'index_id'   => $this->getModel()->getId(),
        ];

        if ($storeId) {
            /** @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full $fullAction */
            $fullAction = $this->context->getObjectManager()
                ->create('Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full', [
                    'data' => $configData,
                ]);
            $indexHandlerFactory = $this->context->getObjectManager()
                ->create('Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory');

            /** @var \Magento\CatalogSearch\Model\Indexer\IndexerHandler $indexHandler */
            $indexHandler = $indexHandlerFactory->create(['data' => $configData]);

            $dimension = new Dimension('scope', $storeId);
            $indexHandler->cleanIndex([$dimension]);
            $indexHandler->saveIndex(
                [$dimension],
                $fullAction->rebuildStoreIndex($storeId)
            );
        } else {
            /** @var \Magento\CatalogSearch\Model\Indexer\Fulltext $fulltext */
            $fulltext = $this->context->getObjectManager()
                ->create('Magento\CatalogSearch\Model\Indexer\Fulltext', [
                    'data' => $configData,
                ]);

            $fulltext->executeFull();
        }

        $this->getModel()
            ->setStatus(IndexInterface::STATUS_READY)
            ->save();

        return true;
    }
}
