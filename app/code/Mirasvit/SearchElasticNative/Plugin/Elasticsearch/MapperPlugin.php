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



namespace Mirasvit\SearchElasticNative\Plugin\Elasticsearch;

use Mirasvit\SearchElasticNative\Adapter\Query\MatchQuery;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Framework\App\ScopeResolverInterface;

use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper;
use Magento\Framework\Search\RequestInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext;

class MapperPlugin 
{
    public function __construct(
        MatchQuery $matchQuery,
		IndexNameResolver $indexNameResolver,
        ScopeResolverInterface $scopeResolver
    ){
    	$this->matchQuery = $matchQuery;
        $this->indexNameResolver = $indexNameResolver;
        $this->scopeResolver = $scopeResolver;
    }

	public function aroundBuildQuery(Mapper $subject, callable $proceed, RequestInterface $request)
	{
        $matchQuery = false;
	    foreach ($request->getQuery()->getShould() as $key => $value) {
	    	$matchQuery = $value;
	    }

        if (!$matchQuery) {
            return $proceed($request);
        }

		$searchQuery = $this->matchQuery->build($matchQuery);
		$result = $proceed($request);

        if ($request->getIndex() != Fulltext::INDEXER_ID) {
            $dimension = current($request->getDimensions());
            $storeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
            $indexName = $request->getFrom()['index_name'];
            $indexName = $this->indexNameResolver->getIndexNameForAlias($storeId, $indexName);
            $result['index'] = $indexName;
            $result['body']['from'] = 0;
        }

        $result['body']['query'] = $searchQuery;

   		return $result;
    }
}
