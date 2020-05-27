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



namespace Mirasvit\Search\Model\Adapter\Mapper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface;
use Magento\Framework\Search\RequestInterface;
use Mirasvit\Search\Api\Data\Index\InstanceInterface;
use Mirasvit\Search\Api\Data\IndexInterface;

/**
 * IndexBuilder for native mysql engine
 */
class IndexBuilder implements IndexBuilderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param IndexScopeResolver $scopeResolver
     */
    public function __construct(
        ResourceConnection $resource,
        IndexScopeResolver $scopeResolver
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function build(RequestInterface $request)
    {
        if (is_array($request->getFrom())) {
            $indexName = $request->getFrom()['index_name'];
        } else {
            $indexName = $request->getIndex();
        }

        $searchIndexTable = $this->scopeResolver->resolve(
            $indexName,
            $request->getDimensions()
        );

        $select = $this->getSelect()
            ->from(
                ['search_index' => $searchIndexTable],
                ['entity_id' => 'entity_id']
            )->joinLeft(
                ['cea' => new \Zend_Db_Expr('(SELECT 1 as search_weight)')],
                '1=1',
                ''
            );

        return $select;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getReadConnection()
    {
        return $this->resource->getConnection();
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->getReadConnection()->select();
    }
}
