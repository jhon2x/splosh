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



namespace Mirasvit\Search\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Mirasvit\Search\Service\StemmingService;
use Magento\Framework\DB\Helper as DbHelper;

class Suggestion extends Template
{
    /**
     * @var QueryFactory
     */
    protected $searchQueryFactory;

    /**
     * @var QueryCollectionFactory
     */
    private $queryCollectionFactory;

    /**
     * @var StemmingService
     */
    private $stemmingService;

    /**
     * @var DbHelper
     */
    private $dbHelper;

    public function __construct(
        QueryCollectionFactory $queryCollectionFactory,
        Context $context,
        QueryFactory $queryFactory,
        DbHelper $dbHelper,
        StemmingService $stemmingService
    ) {
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->searchQueryFactory = $queryFactory;
        $this->dbHelper = $dbHelper;
        $this->stemmingService = $stemmingService;

        parent::__construct($context);
    }

    /**
     * List of enabled indexes
     *
     * @return \Magento\Search\Model\Query[]|\Magento\Search\Model\ResourceModel\Query\Collection
     */
    public function getSuggestedTerms()
    {
        return $this->getSuggestedData();
    }

    /**
     * @return \Magento\Search\Model\ResourceModel\Query\Collection
     */
    private function getSuggestedData()
    {
        $query = $this->searchQueryFactory->get();
        $queryText = $this->stemmingService->singularize($query->getQueryText());
        $collection = $this->queryCollectionFactory->create();

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::FROM)
            ->distinct(true)
            ->from(['main_table' => $collection->getTable('search_query')])
            ->where('num_results > 0')
            ->where('display_in_terms = 1')
            ->where('query_text LIKE ?', $this->dbHelper->addLikeEscape($queryText, ['position' => 'any']))
            ->where('query_text NOT LIKE ?', $this->dbHelper->addLikeEscape('%', ['position' => 'any']))
            ->order('popularity ' . \Magento\Framework\DB\Select::SQL_DESC);

        $collection->addFieldToFilter('query_text', ['nin' => [$query->getQueryText(), $queryText]])
            ->addStoreFilter([$this->_storeManager->getStore()->getId()])
            ->setOrder('popularity')
            ->setPageSize(10)
            ->distinct(true);

        return $collection;
    }
}
