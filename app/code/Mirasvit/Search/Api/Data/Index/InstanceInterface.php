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



namespace Mirasvit\Search\Api\Data\Index;

interface InstanceInterface
{
    const INDEX_PREFIX = 'mst_search_';

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getIndexName();

    /**
     * @return string
     */
    public function getPrimaryKey();

    /**
     * @param string $engine
     * @return DataMapperInterface[]
     */
    public function getDataMappers($engine);

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @return array
     */
    public function getAttributeWeights();

    /**
     * @return $this
     */
    public function reindexAll();

    /**
     * Searchable entities (for reindex)
     *
     * @param int $storeId
     * @param null|array $entityIds
     * @param int $lastEntityId
     * @param int $limit
     * @return \Magento\Framework\Data\Collection\AbstractDb|array
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = 0, $limit = 100);

    /**
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection();
}
