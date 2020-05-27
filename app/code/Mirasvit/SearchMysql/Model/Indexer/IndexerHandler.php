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
 * @package   mirasvit/module-search-mysql
 * @version   1.0.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchMysql\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\IndexStructure;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;

class IndexerHandler implements IndexerInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Resource|Resource
     */
    private $resource;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var ScopeProxy
     */
    private $indexScopeResolver;

    public function __construct(
        IndexRepositoryInterface $indexRepository,
        IndexStructure $indexStructure,
        ResourceConnection $resource,
        Batch $batch,
        array $data,
        $batchSize = 100
    ) {
        $this->indexRepository = $indexRepository;

        $this->indexStructure = $indexStructure;
        $this->resource       = $resource;
        $this->batch          = $batch;
        $this->data           = $data;
        $this->batchSize      = $batchSize;

        if (CompatibilityService::is22() || CompatibilityService::is23()) {
            $this->indexScopeResolver = CompatibilityService::getObjectManager()
                ->create(\Magento\CatalogSearch\Model\Indexer\Scope\ScopeProxy::class);
        } else {
            $this->indexScopeResolver = CompatibilityService::getObjectManager()
                ->create(\Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $index = $this->indexRepository->get($this->getIndexId());

        $instance = $this->indexRepository->getInstance($index);
        foreach ($this->batch->getItems($documents, $this->batchSize) as $docs) {
            foreach ($instance->getDataMappers('mysql2') as $dataMapper) {
                $docs = $dataMapper->map($docs, $dimensions, $this->getIndexName());
            }

            $this->insertDocuments($docs, $dimensions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->resource->getConnection()
                ->delete($this->getTableName($dimensions), ['entity_id in (?)' => $batchDocuments]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexName(), $dimensions);
        $this->indexStructure->create($this->getIndexName(), [], $dimensions);
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable($dimensions = [])
    {
        return true;
    }

    /**
     * @param Dimension[] $dimensions
     * @return string
     */
    private function getTableName($dimensions)
    {
        return $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);
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
            ? $this->data['index_id']
            : $this->indexRepository->get('catalogsearch_fulltext')->getId();
    }

    /**
     * @param array       $documents
     * @param Dimension[] $dimensions
     * @return void
     */
    private function insertDocuments(array $documents, $dimensions)
    {
        $documents = $this->prepareSearchableFields($documents);

        if (empty($documents)) {
            return;
        }
        $this->resource->getConnection()->insertOnDuplicate(
            $this->getTableName($dimensions),
            $documents,
            ['data_index']
        );
    }

    /**
     * @param array $documents
     * @return array
     */
    private function prepareSearchableFields(array $documents)
    {
        $insertDocuments = [];
        foreach ($documents as $entityId => $document) {
            foreach ($document as $attributeId => $fieldValue) {
                $insertDocuments[$entityId . '_' . $attributeId] = [
                    'entity_id'    => $entityId,
                    'attribute_id' => $attributeId,
                    'data_index'   => $fieldValue,
                ];
            }
        }

        return $insertDocuments;
    }
}
