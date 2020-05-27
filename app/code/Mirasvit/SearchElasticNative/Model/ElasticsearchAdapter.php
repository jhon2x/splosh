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



namespace Mirasvit\SearchElasticNative\Model;

use Psr\Log\LoggerInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchNativeAdapter;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Elasticsearch\Model\Adapter\DataMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;

class ElasticsearchAdapter extends ElasticsearchNativeAdapter {
    
    /**
     * {@inheritDoc}
     */
    public function __construct(
        ConnectionManager $connectionManager,
        DataMapperInterface $documentDataMapper,
        FieldMapperInterface $fieldMapper,
        Config $clientConfig,
        BuilderInterface $indexBuilder,
        LoggerInterface $logger,
        IndexNameResolver $indexNameResolver,
        $options = [],
        BatchDataMapperInterface $batchDocumentDataMapper = null
    ) {
        parent::__construct($connectionManager, $documentDataMapper, $fieldMapper,
            $clientConfig, $indexBuilder, $logger, $indexNameResolver, $options,
            $batchDocumentDataMapper);
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareIndex($storeId, $indexName, $mappedIndexerId) 
    {
        parent::prepareIndex($storeId, $indexName, $mappedIndexerId);
        // introduce another level to distinguish by indexer
        unset($this->preparedIndex[$storeId]);
        $this->preparedIndex[$storeId][$mappedIndexerId] = $indexName;
        return $this;
    }

    /**
     * {@inheritDoc}
     * 
     * Modified to support indexer level
     */
    public function updateAlias($storeId, $mappedIndexerId)
    {
        if (!isset($this->preparedIndex[$storeId][$mappedIndexerId])) {
            return $this;
        }

        $oldIndex = $this->indexNameResolver->getIndexFromAlias($storeId, $mappedIndexerId);
        if ($oldIndex == $this->preparedIndex[$storeId][$mappedIndexerId]) {
            $oldIndex = '';
        }

        $this->client->updateAlias(
            $this->indexNameResolver->getIndexNameForAlias($storeId, $mappedIndexerId),
            $this->preparedIndex[$storeId][$mappedIndexerId],
            $oldIndex
        );

        // remove obsolete index
        if ($oldIndex) {
            $this->client->deleteIndex($oldIndex);
        }

        return $this;
    }
}