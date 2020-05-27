<?php

namespace Acidgreen\SploshBackorder\Model\CatalogSearch\Search;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\CatalogSearch\Model\Search\TableMapper;

class IndexBuilder extends \Magento\CatalogSearch\Model\Search\IndexBuilder implements IndexBuilderInterface
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     * @deprecated
     */
    private $storeManager;

    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var TableMapper
     */
    private $tableMapper;

    /**
     * @var ScopeResolverInterface
     */
    private $dimensionScopeResolver;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * SPL-305 - to determine if request is catalogsearch or not?
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param ConditionManager $conditionManager
     * @param IndexScopeResolver $scopeResolver
     * @param TableMapper $tableMapper
     * @param ScopeResolverInterface $dimensionScopeResolver
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\App\Request\Http $httpRequest
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        ConditionManager $conditionManager,
        IndexScopeResolver $scopeResolver,
        TableMapper $tableMapper,
        ScopeResolverInterface $dimensionScopeResolver,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\App\Request\Http $httpRequest
    ) {
        $this->resource = $resource;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->conditionManager = $conditionManager;
        $this->scopeResolver = $scopeResolver;
        $this->tableMapper = $tableMapper;
        $this->dimensionScopeResolver = $dimensionScopeResolver;

        $this->eavAttribute = $eavAttribute;

        /**
         * SPL-305 - for filtering if route name is 'catalogsearch'
         * If it's catalogsearch we don't apply the isShowOutOfStock 
         * logic and backorder logic when searching
         */
        $this->httpRequest = $httpRequest;
    }

    /**
     * Build index query
     *
     * @param RequestInterface $request
     * @return Select
     */
    public function build(RequestInterface $request)
    {
        $searchIndexTable = $this->scopeResolver->resolve($request->getIndex(), $request->getDimensions());
        $select = $this->resource->getConnection()->select()
            ->from(
                ['search_index' => $searchIndexTable],
                ['entity_id' => 'entity_id']
            )
            ->joinLeft(
                ['cea' => $this->resource->getTableName('catalog_eav_attribute')],
                'search_index.attribute_id = cea.attribute_id',
                []
            );

        $select = $this->tableMapper->addTables($select, $request);

        $select = $this->processDimensions($request, $select);

        $isShowOutOfStock = $this->config->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );
        if ($isShowOutOfStock === false) {
        	// get force_backorder products
        	$currentStoreId = $this->storeManager->getStore()->getId();
        	
            /**
             * SPL-349 - Cater to NZ B2B needs
             */
            $b2bStoreIds = [6, 7];
        	if (in_array($currentStoreId, $b2bStoreIds)) {

        		$a = $this->eavAttribute->getIdByCode('catalog_product', 'force_backorder');
        		
        		/** @var \Magento\Framework\DB\Select $fbSelect */
        		$fbSelect = $this->resource->getConnection()->select()
        		->from(['cpei' => 'catalog_product_entity_int'])
        		->where('store_id = ?', $currentStoreId)
        		->where('value = ?', 1)
        		->where('attribute_id = ?', $a);
        		
        		/** @var ResourceConnection $connection */
        		$connection = $this->resource->getConnection();
        		$result = $connection->fetchAll($fbSelect->__toString());
        		
        		$backorderProductRowIds = [];
        		foreach ($result as $r) {
        			$backorderProductRowIds[] = $r['row_id'];
        		}

                /** SPL-428 - get entity_id instead of row_id */
        		$fbSelect = $this->resource->getConnection()->select()
        		->from(['cpe' => 'catalog_product_entity'])
        		->where('row_id IN ('.implode(',', $backorderProductRowIds).')');
        		
        		$result = $connection->fetchAll($fbSelect->__toString());
        		
                $backorderProductIds = [];

                foreach ($result as $r) {
                    $backorderProductIds[] = $r['entity_id'];
                }
        	}
        	
        	
            $select->joinLeft(
                ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
                'search_index.entity_id = stock_index.product_id'
                . $this->resource->getConnection()->quoteInto(
                    ' AND stock_index.website_id = ?',
                    $this->getStockConfiguration()->getDefaultScopeId()
                ),
                []
            );
            /**
             * SPL-305 - Different approach "catalogsearch" is the getRouteName()
             * SPL-321 - Add an "additional parenthesis" to the stock_index.stock_status + backorder queries
             * For non-"catalogsearch" action
             */
            // $select->where('stock_index.stock_status = ?', Stock::DEFAULT_STOCK_ID);

            if ($this->httpRequest->getRouteName() != 'catalogsearch') {
                // IMPROVE THIS IN THE FUTURE PERHAPS?
                $where = '(';
                $where .= '(stock_index.stock_status = '.Stock::DEFAULT_STOCK_ID . ')';
                if (!empty($backorderProductIds))
                    $where .= ' OR (stock_index.stock_status = 0 AND search_index.entity_id IN ('. implode(',', $backorderProductIds). '))';

                $where .= ')';

                $select->where($where);
            } else {
                // TO IMPROVE?
                $searchWhere = '(';
                $searchWhere .= '(stock_index.stock_status = '.Stock::DEFAULT_STOCK_ID.')';
                if (!empty($backorderProductIds))
                    $searchWhere .= ' OR (stock_index.stock_status = 0 AND search_index.entity_id IN ('. implode(',', $backorderProductIds). '))';
                $searchWhere .= ')';

                $select->where($searchWhere);
            }
        }

        return $select;
    }

    /**
     * @return StockConfigurationInterface
     *
     * @deprecated
     */
    private function getStockConfiguration()
    {
        if ($this->stockConfiguration === null) {
            $this->stockConfiguration = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Api\StockConfigurationInterface');
        }
        return $this->stockConfiguration;
    }

    /**
     * Add filtering by dimensions
     *
     * @param RequestInterface $request
     * @param Select $select
     * @return \Magento\Framework\DB\Select
     */
    private function processDimensions(RequestInterface $request, Select $select)
    {
        $dimensions = $this->prepareDimensions($request->getDimensions());

        $query = $this->conditionManager->combineQueries($dimensions, Select::SQL_OR);
        if (!empty($query)) {
            $select->where($this->conditionManager->wrapBrackets($query));
        }

        return $select;
    }

    /**
     * @param Dimension[] $dimensions
     * @return string[]
     */
    private function prepareDimensions(array $dimensions)
    {
        $preparedDimensions = [];
        foreach ($dimensions as $dimension) {
            if ('scope' === $dimension->getName()) {
                continue;
            }
            $preparedDimensions[] = $this->conditionManager->generateCondition(
                $dimension->getName(),
                '=',
                $this->dimensionScopeResolver->getScope($dimension->getValue())->getId()
            );
        }

        return $preparedDimensions;
    }
}

