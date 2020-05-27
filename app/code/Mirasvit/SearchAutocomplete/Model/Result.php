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
 * @package   mirasvit/module-search-autocomplete
 * @version   1.1.96
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchAutocomplete\Model;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Search\Model\QueryFactory;
use Mirasvit\SearchAutocomplete\Api\Repository\IndexRepositoryInterface;

class Result
{
    private $indexRepository;

    /**
     * @var LayerResolver
     */
    private $layerResolver;

    /**
     * @var \Magento\Search\Model\Query
     */
    private $query;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SearchHelper
     */
    private $searchHelper;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var bool
     */
    private static $isLayerCreated = false;

    public function __construct(
        IndexRepositoryInterface $indexRepository,
        LayerResolver $layerResolver,
        QueryFactory $queryFactory,
        Config $config,
        SearchHelper $searchHelper
    ) {
        $this->indexRepository = $indexRepository;
        $this->layerResolver   = $layerResolver;
        $this->queryFactory    = $queryFactory;
        $this->config          = $config;
        $this->searchHelper    = $searchHelper;
    }

    /**
     * @return void
     */
    public function init()
    {
        $this->query = $this->queryFactory->get();
        if (!self::$isLayerCreated) {
            try {
                $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);
            } catch (\Exception $e) {
            } finally {
                self::$isLayerCreated = true;
            }
        }
    }

    /**
     * Convert all results to array
     * @return array
     * @SuppressWarnings(PHPMD)
     */
    public function toArray()
    {
        $result = [
            'totalItems' => 0,
            'query'      => $this->query->getQueryText(),
            'indices'    => [],
            'noResults'  => false,
            'urlAll'     => $this->searchHelper->getResultUrl($this->query->getQueryText()),
            'optimize'   => boolval($this->config->isOptimizeMobile()),
        ];

        $customInstances = [
            'magento_search_query',
            'magento_catalog_categoryproduct',
        ];

        $totalItems = 0;

        foreach ($this->indexRepository->getIndices() as $index) {
            $identifier = $index->getIdentifier();

            if (!$this->config->getIndexOptionValue($identifier, 'is_active')) {
                continue;
            }

            $index->addData($this->config->getIndexOptions($identifier));

            $instance = $this->indexRepository->getInstance($identifier);
            if (!$instance) {
                continue;
            }
            $instance->setIndex($index)
                ->setLimit($this->config->getIndexOptionValue($identifier, 'limit'))
                ->setRepository($this->indexRepository);

            $items = $instance->getItems();
            $size  = $instance->getSize();

            $result['indices'][] = [
                'identifier'   => $identifier == 'catalogsearch_fulltext' ? 'magento_catalog_product' : $identifier,
                'title'        => $this->htmlEntityDecode((string)__($index->getTitle())),
                'order'        => (int)$this->config->getIndexOptionValue($identifier, 'order'),
                'items'        => $items,
                'totalItems'   => $size,
                'isShowTotals' => in_array($identifier, $customInstances) ? false : true,
            ];

            $totalItems += $size;

            if (!in_array($identifier, $customInstances)) {
                $result['totalItems'] += $size;
            }
        }

        usort($result['indices'], function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        if ($this->config->getAutocompleteLayout() == Config::LAYOUT_2_COLUMNS) {
            foreach ($result['indices'] as $key => $index) {
                if ($index['identifier'] == 'magento_catalog_product') {
                    $productFirst = $result['indices'][$key];
                    unset($result['indices'][$key]);
                    array_unshift($result['indices'], $productFirst);
                }
            }
        }

        $result['textAll']   = $this->htmlEntityDecode(__('Show all %1 results →', $result['totalItems']));
        $result['textEmpty'] = $this->htmlEntityDecode(__('Sorry, nothing found for "%1".', $result['query']));

        $result['noResults'] = $totalItems ? false : true;

        $this->query->setNumResults($result['totalItems']);

        return $result;
    }

    /**
     * @param string $text
     * @return string
     */
    private function htmlEntityDecode($text) 
    {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
