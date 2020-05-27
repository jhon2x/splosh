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



namespace Mirasvit\SearchElasticNative\Model\Index;

use Magento\Framework\Search\Request\Dimension;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver as ElasticIndexNameResolver;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\Config;
use Psr\Log\LoggerInterface;
use Mirasvit\Core\Service\CompatibilityService;

class IndexNameResolver extends ElasticIndexNameResolver 
{
    /**
     * @var ScopeProxy|IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Config $clientConfig,
        LoggerInterface $logger,
        $options = []
    ) {
        parent::__construct($connectionManager, $clientConfig, $logger, $options);
        $this->indexScopeResolver = CompatibilityService::getObjectManager()
            ->create('Magento\CatalogSearch\Model\Indexer\Scope\ScopeProxy');
    }

    /**
     * {@inheritDoc}
     * Added support for indexer level to @param array $preparedIndex
     */
    public function getIndexName($storeId, $mappedIndexerId, array $preparedIndex) 
    {
        if (isset($preparedIndex[$storeId][$mappedIndexerId])) {
            return $preparedIndex[$storeId][$mappedIndexerId];
        } else {
            $indexName = $this->getIndexFromAlias($storeId, $mappedIndexerId);
            if (empty($indexName)) {
                $indexName = $this->getIndexPattern($storeId, $mappedIndexerId) . 1;
            }
        }
        return $indexName;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexNameForAlias($storeId, $mappedIndexerId) 
    {
        if ($mappedIndexerId == Config::ELASTICSEARCH_TYPE_DEFAULT) {
            return parent::getIndexNameForAlias($storeId, $mappedIndexerId);
        } else {
            $dimension = new Dimension('scope', $storeId);
            $indexName = $this->indexScopeResolver->resolve($mappedIndexerId, [$dimension]);
            return $this->getIndexNamespace() . '_' . $indexName;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexPattern($storeId, $mappedIndexerId) 
    {
        if ($mappedIndexerId == Config::ELASTICSEARCH_TYPE_DEFAULT) {
            return parent::getIndexPattern($storeId, $mappedIndexerId);
        } else {
            $dimension = new Dimension('scope', $storeId);
            $indexName = $this->indexScopeResolver->resolve($mappedIndexerId, [$dimension]);
            return $this->getIndexNamespace() . '_' . $indexName . '_v';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexFromAlias($storeId, $mappedIndexerId) 
    {
        if ($mappedIndexerId == Config::ELASTICSEARCH_TYPE_DEFAULT) {
            return parent::getIndexFromAlias($storeId, $mappedIndexerId);
        } else {
            $storeIndex = '';
            $indexPattern = $this->getIndexPattern($storeId, $mappedIndexerId);
            $namespace = $this->getIndexNameForAlias($storeId, $mappedIndexerId);
            if ($this->client->existsAlias($namespace)) {
                $alias = $this->client->getAlias($namespace);
                $indices = array_keys($alias);
                foreach ($indices as $index) {
                    if (strpos($index, $indexPattern) === 0) {
                        $storeIndex = $index;
                        break;
                    }
                }
            }
            return $storeIndex;
        }
    }

}