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

use Mirasvit\Search\Api\Data\Index\DataMapperInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity as EavEntity;
use Magento\Framework\App\ResourceConnection;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category\Tree;

class DataMapper implements DataMapperInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @var EavEntity
     */
    private $eavEntity;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Catalog\Model\Category\Tree
     */
    private $categoryTree;


    public function __construct(
        IndexRepositoryInterface $indexRepository,
        Attribute $attribute,
        EavEntity $eavEntity,
        ResourceConnection $resource,
        Tree $categoryTree
    ) {
        $this->indexRepository = $indexRepository;
        $this->attribute = $attribute;
        $this->eavEntity = $eavEntity;
        $this->resource = $resource;
        $this->connection = $this->resource->getConnection();
        $this->categoryTree = $categoryTree;
    }

    /**
     * {@inheritdoc}
     */
    public function map(array $documents, $dimensions, $indexIdentifier)
    {
        $this->activeCategoriesOnly($documents);
        $this->addCategoryData($documents);
        $this->addCustomOptions($documents);
        $this->addBundledOptions($documents);
        $this->addProductIdData($documents);

        return $documents;
    }

    /**
     * @param array &$index
     * @return $this
     */
    protected function activeCategoriesOnly(&$index)
    {
        if (!$this->getIndex()->getProperty('only_active_categories')) {
            return $this;
        }

        if (!$this->categoryTree->getRootNode()) {
            return $this;
        }

        $root = $this->categoryTree->getTree($this->categoryTree->getRootNode())->getChildrenData();
        $ids = $this->getActiveCategories($root);
        $activeCategories = '(' . implode(',', $ids) . ')';
        $productIds = '(' . implode(',', array_keys($index)) . ')';

        $select = $this->connection->select()
            ->from([$this->resource->getTableName('catalog_category_product')], ['product_id'])
            ->where('product_id IN '. trim($productIds))
            ->where('category_id != (?)', $this->categoryTree->getRootNode()->getId())
            ->where('category_id IN '. trim($activeCategories))
            ->group('product_id');

        foreach (array_diff(array_keys($index), $this->connection->fetchCol($select)) as $productId) {
            unset($index[$productId]);
        }

        return $this;
    }

    /**
     * @param array &$index
     * @return $this
     */
    protected function addCustomOptions(&$index)
    {
        if (!$this->getIndex()->getProperty('include_custom_options')) {
            return $this;
        }

        $productIds = array_keys($index);
        $this->connection->query('SET SESSION group_concat_max_len = 1000000;');

        $select = $this->connection->select()
            ->from(['main_table' => $this->resource->getTableName('catalog_product_option')], ['product_id'])
            ->joinLeft(
                ['otv' => $this->resource->getTableName('catalog_product_option_type_value')],
                'main_table.option_id = otv.option_id',
                ['sku' => new \Zend_Db_Expr("GROUP_CONCAT(otv.`sku` SEPARATOR ' ')")]
            )
            ->joinLeft(
                ['ott' => $this->resource->getTableName('catalog_product_option_type_title')],
                'otv.option_type_id = ott.option_type_id',
                ['title' => new \Zend_Db_Expr("GROUP_CONCAT(ott.`title` SEPARATOR ' ')")]
            )
            ->where('main_table.product_id IN (?)', $productIds)
            ->group('product_id');

        foreach ($this->connection->fetchAll($select) as $row) {
            if (!isset($index[$row['product_id']]['options'])) {
                $index[$row['product_id']]['options'] = '';
            }
            $index[$row['product_id']]['options'] .= ' ' . $row['title'];
            $index[$row['product_id']]['options'] .= ' ' . $row['sku'];
        }

        return $this;
    }

    /**
     * @param array &$index
     * @return $this
     */
    protected function addBundledOptions(&$index)
    {
        if (!$this->getIndex()->getProperty('include_bundled')) {
            return $this;
        }

        $productIds = array_keys($index);
        $this->connection->query('SET SESSION group_concat_max_len = 1000000;');

        $select = $this->connection->select()
            ->from(
                ['main_table' => $this->resource->getTableName('catalog_product_entity')],
                ['sku' => new \Zend_Db_Expr("GROUP_CONCAT(main_table.`sku` SEPARATOR ' ')")]
            )
            ->group('cpr.parent_id');

        // enterprise
        $tbl = $this->connection->describeTable($this->resource->getTableName('catalog_product_entity'));
        if (isset($tbl['row_id'])) {
            $select
                ->joinLeft(
                    ['cpr' => $this->resource->getTableName('catalog_product_relation')],
                    'main_table.entity_id = cpr.child_id',
                    []
                )->joinLeft(
                    ['cpe' => $this->resource->getTableName('catalog_product_entity')],
                    'cpe.row_id = cpr.parent_id',
                    ['parent_id' => 'entity_id']
                )->where('cpe.entity_id IN (?)', $productIds);
        } else {
            $select
                ->joinLeft(
                    ['cpr' => $this->resource->getTableName('catalog_product_relation')],
                    'main_table.entity_id = cpr.child_id',
                    ['parent_id']
                )
                ->where('cpr.parent_id IN (?)', $productIds);
        }

        foreach ($this->connection->fetchAll($select) as $row) {
            if (!isset($index[$row['parent_id']]['options'])) {
                $index[$row['parent_id']]['options'] = '';
            }
            if (is_array($index[$row['parent_id']]['options'])) {
                $index[$row['parent_id']]['options'] = implode(' ', $index[$row['parent_id']]['options']);
            }

            $index[$row['parent_id']]['options'] .= ' ' . $row['sku'];
        }

        return $this;
    }

    /**
     * @param array &$index
     * @return $this
     */
    protected function addProductIdData(&$index)
    {
        if (!$this->getIndex()->getProperty('include_id')) {
            return $this;
        }

        foreach ($index as $entityId => &$data) {
            if (!isset($data['options'])) {
                $data['options'] = '';
            }

            $data['options'] .= ' ' . $entityId;
        }

        return $this;
    }

    /**
     * @param array &$index
     * @return $this
     */
    protected function addCategoryData(&$index)
    {
        if (!$this->getIndex()->getProperty('include_category')) {
            return $this;
        }

        $entityTypeId = $this->eavEntity->setType(Category::ENTITY)->getTypeId();

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $this->attribute->loadByCode($entityTypeId, 'name');

        $tbl = $this->connection->describeTable($attribute->getBackend()->getTable());

        $pk = 'entity_id';

        if (isset($tbl['row_id'])) {
            $pk = 'row_id';
        }

        $productIds = array_keys($index);

        $valueSelect = $this->connection->select()
            ->from(
                ['cc' => $this->resource->getTableName('catalog_category_entity')],
                [new \Zend_Db_Expr("GROUP_CONCAT(vc.value SEPARATOR ' ')")]
            )
            ->joinLeft(
                ['vc' => $attribute->getBackend()->getTable()],
                'cc.entity_id = vc.' . $pk,
                []
            )
            ->where("LOCATE(CONCAT('/', CONCAT(cc.entity_id, '/')), CONCAT(ce.path, '/'))")
            ->where('vc.attribute_id = ?', $attribute->getId());

        $columns = [
            'product_id' => 'product_id',
            'category'   => new \Zend_Db_Expr('(' . $valueSelect . ')'),
        ];

        $select = $this->connection->select()
            ->from([$this->resource->getTableName('catalog_category_product')], $columns)
            ->joinLeft(
                ['ce' => $this->resource->getTableName('catalog_category_entity')],
                'category_id = ce.entity_id',
                []
            )
            ->where('product_id IN (?)', $productIds);

        foreach ($this->connection->fetchAll($select) as $row) {
            if (!isset($index[$row['product_id']]['options'])) {
                $index[$row['product_id']]['options'] = '';
            }

            if (is_array($index[$row['product_id']]['options'])) {
                $index[$row['product_id']]['options'] = implode(' ', $index[$row['product_id']]['options']);
            }
            $index[$row['product_id']]['options'] .= ' ' . $row['category'];
        }

        return $this;
    }

    /**
     * @param array root
     * @return array $result
     */
    private function getActiveCategories($root = null)
    {
        $result = [];
        foreach ($root as $item) {
            if ($item->getIsActive()) {
                $result[] = $item->getId();
            }
            if ($item->getChildrenData()) {
                foreach ($this->getActiveCategories($item->getChildrenData()) as $id) {
                    $result[] = $id;
                }
            }
        }
        return $result;
    }

    /**
     * @return \Mirasvit\Search\Api\Data\IndexInterface
     */
    public function getIndex()
    {
        return $this->indexRepository->get('catalogsearch_fulltext');
    }
}
