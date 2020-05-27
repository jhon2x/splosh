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

use Magento\Framework\Encryption\UrlCoder;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\QueryFactory;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\Search\Api\Service\IndexServiceInterface;
use Mirasvit\Search\Model\Config;
use Mirasvit\Search\Model\ResourceModel\Index\CollectionFactory as IndexCollectionFactory;

class Result extends Template
{
    /**
     * @var IndexCollectionFactory
     */
    protected $indexRepository;

    /**
     * @var IndexServiceInterface
     */
    protected $indexService;

    /**
     * @var QueryFactory
     */
    protected $searchQueryFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var IndexInterface[]
     */
    protected $indices;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var UrlCoder
     */
    private $urlCoder;

    public function __construct(
        Context $context,
        IndexRepositoryInterface $indexRepository,
        IndexServiceInterface $indexService,
        QueryFactory $queryFactory,
        Config $config,
        Registry $registry,
        UrlCoder $urlCoder
    ) {
        $this->indexRepository    = $indexRepository;
        $this->indexService       = $indexService;
        $this->config             = $config;
        $this->searchQueryFactory = $queryFactory;
        $this->registry           = $registry;
        $this->urlCoder           = $urlCoder;

        parent::__construct($context);
    }

    /**
     * List of enabled indexes
     * @return IndexInterface[]
     */
    public function getIndices()
    {
        if ($this->indices == null) {
            $result = [];

            $collection = $this->indexRepository->getCollection()
                ->addFieldToFilter(IndexInterface::IS_ACTIVE, 1)
                ->setOrder(IndexInterface::POSITION, 'asc');

            /** @var IndexInterface $index */
            foreach ($collection as $index) {
                $index = $this->indexRepository->get($index->getId());

                if ($this->config->isMultiStoreModeEnabled()
                    && $index->getIdentifier() == 'catalogsearch_fulltext'
                ) {
                    foreach ($this->_storeManager->getStores(false, true) as $code => $store) {
                        if (in_array($store->getId(), $this->config->getEnabledMultiStores())) {
                            $clone = clone $index;
                            $clone->setData('store_id', $store->getId());
                            $clone->setData('store_code', $code);
                            if ($this->_storeManager->getStore()->getId() != $store->getId()) {
                                $clone->setData('title', $store->getName());
                            }
                            $result[] = $clone;
                        }
                    }
                } else {
                    $result[] = $index;
                }
            }

            $this->indices = $result;
        }

        return $this->indices;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Current content
     * @return string
     */
    public function getCurrentContent()
    {
        $index = $this->getCurrentIndex();
        $html  = $this->getContentBlock($index)->toHtml();

        return $html;
    }

    /**
     * Block for index model
     *
     * @param IndexInterface $index
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @throws \Exception
     */
    public function getContentBlock($index)
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->getChildBlock($index->getIdentifier());

        if ($index->getIdentifier() == 'catalogsearch_fulltext') {
            $block = $this->_layout->getBlock('search.result');
        }

        if (!$block) {
            throw new \Exception(__('Child block %1 not exists', $index->getIdentifier()));
        }

        $block->setIndex($index);

        return $block;
    }

    /**
     * First matched index model
     * @return IndexInterface
     */
    public function getFirstMatchedIndex()
    {
        foreach ($this->getIndices() as $index) {
            if (($index->getData('store_id') == false
                || $index->getData('store_id') == $this->getCurrentStore()->getId())
            ) {
                return $index;
            }
        }

        return $this->getIndices()[0];
    }

    /**
     * Current index model
     * @return IndexInterface
     */
    public function getCurrentIndex()
    {
        $indexId = $this->getRequest()->getParam('index');

        if ($indexId) {
            foreach ($this->getIndices() as $index) {
                if ($index->getId() == $indexId) {
                    return $index;
                }
            }
        }

        return $this->getFirstMatchedIndex();
    }

    /**
     * Current index size
     * @return int
     */
    public function getCurrentIndexSize()
    {
        return $this->getSearchCollection($this->getCurrentIndex())->getSize();
    }

    /**
     * @param IndexInterface $index
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getSearchCollection(IndexInterface $index)
    {
        return $this->indexService->getSearchCollection($index);
    }

    /**
     * Index url
     *
     * @param \Mirasvit\Search\Model\Index $index
     *
     * @return string
     */
    public function getIndexUrl($index)
    {
        $query = [
            'index' => $index->getId(),
            'p'     => null,
        ];

        if ($index->hasData('store_id')
            && $index->getData('store_id') != $this->getCurrentStore()->getId()
        ) {
            $query['q'] = $this->getRequest()->getParam('q');
            $query['___store'] = $this->getRequest()->getParam('q');
            $query['___from_store'] = $this->getCurrentStore()->getCode();

            $uenc = $this->_storeManager->getStore($index->getData('store_id'))->getUrl('catalogsearch/result',
                ['_query' => $query]);

            return $this->getUrl('stores/store/switch', [
                '_query' => [
                    '___store' => $index->getData('store_code'),
                    '___from_store' => $this->getCurrentStore()->getCode(),
                    'uenc' => $this->urlCoder->encode($uenc),
                ],
            ]);
        }

        $params = [
            '_current' => true,
            '_query'   => $query,
        ];

        if ($index->hasData('store_id')) {
            $params['_scope'] = $index->getData('store_id');
        }

        return $this->getUrl('*/*/*', $params);
    }

    /**
     * Save number of results + highlight
     *
     * @param string $html
     *
     * @return string
     */
    protected function _afterToHtml($html)
    {
        $numResults = 0;

        foreach ($this->getIndices() as $index) {
            $numResults += $this->getSearchCollection($index)->getSize();
        }

        $this->registry->register('QueryTotalCount', $numResults, true);

        $this->searchQueryFactory->get()
            ->saveNumResults($numResults);

        return $html;
    }

    /**
     * @return int
     */
    public function getMinCollectionSize()
    {
        return (int)Config::MIN_COLLECTION_SIZE;
    }
}
