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
 * @package   mirasvit/module-search-sphinx
 * @version   1.1.49
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchSphinx\Model\Search;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogSearch\Model\Search\TableMapper;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\SearchSphinx\Adapter\MapperQL;
use Magento\Framework\App\ObjectManager;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;
use Magento\CatalogSearch\Model\Search\BaseSelectStrategy\BaseSelectStrategyInterface;
use Magento\Framework\DB\Select;
use Magento\CatalogInventory\Model\Stock;

/**
 * @SuppressWarnings(PHPMD)
 */
class CatalogSearchIndexBuilder extends \Magento\CatalogSearch\Model\Search\IndexBuilder
{
    /**
     * @var MapperQL
     */
    private $mapperQL;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DimensionsProcessor
     */
    private $dimensionsProcessor;

    /**
     * @var SelectContainerBuilder
     */
    private $selectContainerBuilder;

    /**
     * @var BaseSelectStrategyMapper
     */
    private $baseSelectStrategyMapper;

    /**
     * @var FilterMapper
     */
    private $filterMapper;

    /**
     * @var TableMapper
     */
    private $tableMapper;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var ScopeResolverInterface
     */
    private $dimensionScopeResolver;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    public function __construct(
        MapperQL $mapperQL,
        ResourceConnection $resource,
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        ConditionManager $conditionManager,
        IndexScopeResolver $scopeResolver,
        TableMapper $tableMapper,
        ScopeResolverInterface $dimensionScopeResolver,
        $dimensionsProcessor = null,
        $selectContainerBuilder = null,
        $baseSelectStrategyMapper = null,
        $filterMapper = null
    ) {
        $this->tableMapper = $tableMapper;
        $this->config = $config;
        $this->conditionManager = $conditionManager;
        $this->dimensionScopeResolver = $dimensionScopeResolver;
        $this->mapperQL = $mapperQL;
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->storeManager = $storeManager;

        if (CompatibilityService::is21()) {
            parent::__construct(
                $resource,
                $config,
                $storeManager,
                $conditionManager,
                $scopeResolver,
                $tableMapper,
                $dimensionScopeResolver
            );
        } else {
            $this->dimensionsProcessor = $dimensionsProcessor ?: ObjectManager::getInstance()
                ->get('\Magento\CatalogSearch\Model\Search\FilterMapper\DimensionsProcessor');

            $this->selectContainerBuilder = $selectContainerBuilder ?: ObjectManager::getInstance()
                ->get('Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainerBuilder');

            $this->baseSelectStrategyMapper = $baseSelectStrategyMapper ?: ObjectManager::getInstance()
                ->get('Magento\CatalogSearch\Model\Search\BaseSelectStrategy\StrategyMapper');

            $this->filterMapper = $filterMapper ?: ObjectManager::getInstance()
                ->get('Magento\CatalogSearch\Model\Search\FilterMapper\FilterMapper');

            parent::__construct(
                $resource,
                $config,
                $storeManager,
                $conditionManager,
                $scopeResolver,
                $tableMapper,
                $dimensionScopeResolver,
                $dimensionsProcessor,
                $selectContainerBuilder,
                $baseSelectStrategyMapper,
                $filterMapper
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function build(RequestInterface $request)
    {
        if (CompatibilityService::is21()) {
            $table = $this->mapperQL->buildQuery($request);
            $select = $this->resource->getConnection()->select()
                ->from(
                    ['search_index' => $table->getName()],
                    ['entity_id' => 'entity_id', 'score' => 'score']
                );
            $select = $this->tableMapper->addTables($select, $request);
            $select = $this->processDimensions($request, $select);
            $isShowOutOfStock = $this->config->isSetFlag(
                'cataloginventory/options/show_out_of_stock',
                ScopeInterface::SCOPE_STORE
            );
            if ($isShowOutOfStock === false) {
                $select->joinLeft(
                    ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
                    'search_index.entity_id = stock_index.product_id'
                    . $this->resource->getConnection()->quoteInto(
                        ' AND stock_index.website_id = ?',
                        $this->getStockConfiguration()->getDefaultScopeId()
                    ),
                    []
                );
                $select->where('stock_index.stock_status = ?', Stock::DEFAULT_STOCK_ID);
            }
            return $select;
        } else {
            /** @var SelectContainer $selectContainer */
            $selectContainer = $this->selectContainerBuilder->buildByRequest($request);
            /** @var BaseSelectStrategyInterface $baseSelectStrategy */
            $baseSelectStrategy = $this->baseSelectStrategyMapper->mapSelectContainerToStrategy($selectContainer);

            $selectContainer = $this->createBaseSelect($selectContainer, $request);
            $selectContainer = $this->filterMapper->applyFilters($selectContainer);

            $selectContainer = $this->dimensionsProcessor->processDimensions($selectContainer);

            return $selectContainer->getSelect();
        }
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
     * @param array $dimensions
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

    public function createBaseSelect(SelectContainer $selectContainer, RequestInterface $request)
    {
        $select = $this->resource->getConnection()->select();
        $mainTableAlias = $selectContainer->isFullTextSearchRequired() ? 'eav_index' : 'search_index';

        $select->distinct()
            ->from(
                [$mainTableAlias => $this->resource->getTableName('catalog_product_index_eav')],
                ['entity_id' => 'entity_id']
            )->where(
                $this->resource->getConnection()->quoteInto(
                    sprintf('%s.store_id = ?', $mainTableAlias),
                    $this->storeManager->getStore()->getId()
                )
            );

        //        if ($selectContainer->isFullTextSearchRequired()) {
        $table = $this->mapperQL->buildQuery($request);

        $select->joinInner(
            ['search_index' => $table->getName()],
            'eav_index.entity_id = search_index.entity_id',
            ['score']
        );
        //        }

        $selectContainer = $selectContainer->updateSelect($select);

        return $selectContainer;
    }
}
