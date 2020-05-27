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


namespace Mirasvit\Search\Plugin;

use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
use Magento\Framework\Search\Request\QueryInterface;

class EmptySearchQueryPlugin {

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ResponseFactory $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    public function aroundSearch(SearchEngineInterface $subject, callable $proceed, 
        RequestInterface $request)
    {
        if ($request->getName() == 'quick_search_container' && 
                !$this->hasSearchQuery($request)) {
            return $this->responseFactory->create($this->getEmptyResult());
        }
        return $proceed($request);
    }

    /**
     * @param RequestInterface $request
     * @return boolean
     */
    private function hasSearchQuery(RequestInterface $request) 
    {
        $query = $request->getQuery();
        if ($query->getType() == QueryInterface::TYPE_BOOL) {
            return isset($query->getShould()['search']) 
                && !empty($query->getShould()['search']);
        }
    }

    /**
     * @return array
     */
    private function getEmptyResult() 
    {
        return [
            'documents' => [],
            'aggregations' => [
                'price_bucket' => [],
                'category_bucket' => []
            ],
            'total' => 0
        ];
    }

}