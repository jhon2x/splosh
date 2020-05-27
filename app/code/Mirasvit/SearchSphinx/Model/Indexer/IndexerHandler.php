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



namespace Mirasvit\SearchSphinx\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\Search\Model\IndexFactory as SearchIndexFactory;
use Mirasvit\SearchSphinx\Model\Engine;
use Mirasvit\Search\Model\Index\Magento\Catalog\Product\Prepare;

class IndexerHandler implements IndexerInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var IndexScopeResolverInterface
     */
    private $indexScopeResolver;

    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    public function __construct(
        IndexRepositoryInterface $indexRepository,
        Batch $batch,
        IndexScopeResolver $indexScopeResolver,
        Engine $engine,
        array $data,
        $batchSize = 1000
    ) {
        $this->indexRepository = $indexRepository;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->batch = $batch;
        $this->data = $data;
        $this->engine = $engine;

        $this->batchSize = $batchSize;
    }

    /**
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @param \Traversable $documents
     * @return void
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $index = $this->indexRepository->get($this->getIndexId());

        $instance = $this->indexRepository->getInstance($index);

        $indexName = $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);

        foreach ($this->batch->getItems($documents, $this->batchSize) as $docs) {
            foreach ($instance->getDataMappers('sphinx') as $mapper) {
                $docs = $mapper->map($docs, $dimensions, $this->getIndexName(), $instance);
            }

            $this->engine->saveDocuments($index, $indexName, $docs);
        }
    }

    /**
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @param \Traversable $documents
     * @return void
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $index = $this->indexRepository->get($this->getIndexId());
        $indexName = $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);

        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->engine->deleteDocuments($index, $indexName, $batchDocuments);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex($dimensions)
    {
        $indexName = $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);
        $this->engine->cleanIndex($indexName);
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable($dimensions = [])
    {
        return true;
    }

    /**
     * @return string
     */
    private function getIndexName()
    {
        return $this->data['indexer_id'];
    }

    /**
     * @return string
     */
    private function getIndexId()
    {
        return isset($this->data['index_id'])
            ? $this->data['index_id'] :
            $this->indexRepository->get('catalogsearch_fulltext')->getId();
    }
}
