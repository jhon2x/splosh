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



namespace Mirasvit\SearchSphinx\Adapter;

use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\SearchSphinx\SphinxQL\Expression as QLExpression;
use Mirasvit\SearchSphinx\SphinxQL\SphinxQL;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Adapter\Mysql\DocumentFactory as MysqlDocumentFactory;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;
use Mirasvit\Search\Model\IndexFactory;
use Mirasvit\SearchSphinx\Adapter\Query\Builder\Match;
use Mirasvit\SearchSphinx\Adapter\Query\QueryContainer;
use Mirasvit\SearchSphinx\Adapter\Query\QueryContainerFactory;
use Mirasvit\SearchSphinx\Model\Engine;
use Magento\Framework\DataObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MapperQL
{
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        QueryContainerFactory $queryContainerFactory,
        Match $matchBuilder,
        TemporaryStorageFactory $temporaryStorageFactory,
        IndexScopeResolver $scopeResolver,
        Engine $engine,
        MysqlDocumentFactory $documentFactory
    ) {
        $this->queryContainerFactory = $queryContainerFactory;
        $this->matchBuilder = $matchBuilder;
        $this->temporaryStorage = $temporaryStorageFactory->create();
        $this->indexRepository = $indexRepository;
        $this->scopeResolver = $scopeResolver;
        $this->engine = $engine;
        $this->documentFactory = $documentFactory;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\DB\Ddl\Table
     * @throws \Exception
     */
    public function buildQuery(RequestInterface $request)
    {
        if (is_array($request->getFrom())) {
            $searchIndex = $this->indexRepository->get($request->getFrom()['index_id']);
            $indexName = $this->scopeResolver->resolve(
                $request->getFrom()['index_name'],
                $request->getDimensions()
            );
        } else {
            $searchIndex = $this->indexRepository->get($request->getIndex());
            $indexName = $this->scopeResolver->resolve(
                $searchIndex->getIdentifier(),
                $request->getDimensions()
            );
        }

        $weights = [];
        foreach ($this->indexRepository->getInstance($searchIndex)->getAttributeWeights() as $attr => $weight) {
            if (!is_numeric(substr($attr, 0, 1))) {
                $weights[$attr] = pow(2, (int)$weight);
            }
        }

        $sphinxQuery = $this->engine->getQuery()
            ->select(['id', new QLExpression('weight()')])
            ->from($indexName)
            ->limit(0, 1000000)
            ->option('max_matches', 1000000)
            ->option('field_weights', $weights)
            ->option('ranker', new QLExpression("expr('sum(1/min_hit_pos*user_weight 
                + word_count*user_weight + exact_hit*user_weight*1000 + lcs*user_weight) * 1000 + bm25')"));

        $queryContainer = $this->queryContainerFactory->create(['request' => $request]);

        $sphinxQuery = $this->processQuery(
            $request->getQuery(),
            $sphinxQuery,
            BoolQuery::QUERY_CONDITION_MUST,
            $queryContainer
        );

        $sphinxQuery = $this->addDerivedQueries(
            $queryContainer,
            $sphinxQuery
        );

        $result = $sphinxQuery->execute();

        if (filter_input(INPUT_GET,'debug') !== null) {
            // @codingStandardsIgnoreStart
            var_dump($sphinxQuery->getCompiled());
            var_dump($result);
            // @codingStandardsIgnoreEnd
        }

        $documents = [];

        foreach ($result as $item) {
            $document = $this->documentFactory->create([]);
            $document
                ->setId($item['id'])
                ->setCustomAttribute('entity_id', $item['id'])
                ->setCustomAttribute('score', new DataObject(['value' => $item['weight()']]));
            $documents[] = $document;
        }

        $table = $this->temporaryStorage->storeApiDocuments($documents);

        return $table;
    }

    /**
     * @param RequestQueryInterface $query
     * @param SphinxQL $select
     * @param  string $conditionType
     * @param QueryContainer $queryContainer
     * @return SphinxQL
     */
    protected function processQuery(
        RequestQueryInterface $query,
        SphinxQL $select,
        $conditionType,
        QueryContainer $queryContainer
    ) {
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_MATCH:
                $select = $queryContainer->addMatchQuery(
                    $select,
                    $query,
                    $conditionType
                );
                break;
            case RequestQueryInterface::TYPE_BOOL:
                $select = $this->processBoolQuery($query, $select, $queryContainer);
                break;
        }

        return $select;
    }

    /**
     * @param BoolQuery $query
     * @param SphinxQL $select
     * @param QueryContainer $queryContainer
     * @return SphinxQL
     */
    private function processBoolQuery(
        BoolQuery $query,
        SphinxQL $select,
        QueryContainer $queryContainer
    ) {
        $select = $this->processBoolQueryCondition(
            $query->getMust(),
            $select,
            BoolQuery::QUERY_CONDITION_MUST,
            $queryContainer
        );

        $select = $this->processBoolQueryCondition(
            $query->getShould(),
            $select,
            BoolQuery::QUERY_CONDITION_SHOULD,
            $queryContainer
        );

        $select = $this->processBoolQueryCondition(
            $query->getMustNot(),
            $select,
            BoolQuery::QUERY_CONDITION_NOT,
            $queryContainer
        );

        return $select;
    }

    /**
     * @param array $subQueryList
     * @param SphinxQL $select
     * @param string $conditionType
     * @param QueryContainer $queryContainer
     * @return SphinxQL
     */
    private function processBoolQueryCondition(
        array $subQueryList,
        SphinxQL $select,
        $conditionType,
        QueryContainer $queryContainer
    ) {
        foreach ($subQueryList as $subQuery) {
            $select = $this->processQuery($subQuery, $select, $conditionType, $queryContainer);
        }

        return $select;
    }

    /**
     * @param QueryContainer $queryContainer
     * @param SphinxQL $select
     * @return SphinxQL
     */
    private function addDerivedQueries(
        QueryContainer $queryContainer,
        SphinxQL $select
    ) {
        $matchQueries = $queryContainer->getMatchQueries();

        if ($matchQueries) {
            $matchContainer = array_shift($matchQueries);
            $select = $this->matchBuilder->build(
                $select,
                $matchContainer->getRequest(),
                $matchContainer->getConditionType()
            );
        }

        return $select;
    }
}
