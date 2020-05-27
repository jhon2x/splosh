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



namespace Mirasvit\Search\Controller\Adminhtml\Validator;

use Mirasvit\Search\Controller\Adminhtml\Validator;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;


use Magento\Search\Model\SearchEngine;
use Magento\CatalogSearch\Model\Advanced\Request\BuilderFactory as RequestBuilderFactory;
use Magento\Framework\App\ScopeResolverInterface;

use Magento\Catalog\Model\ProductFactory;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Catalog\Model\Product\Visibility;

use Mirasvit\Search\Index\Magento\Catalog\Product\Index as ProductIndex;

class ValidateSearchResults extends Validator
{
    protected $context;

    protected $indexRepository;
    protected $searchEngine;
    protected $requestBuilderFactory;
    protected $scopeResolver;
    protected $productRepository;
    protected $productIndex;
    private $proceedTest = true;
    private $result = [];
    private $status = self::STATUS_SUCCESS;
    private $product;
    private $query;

    private $resultJsonFactory;

    public function __construct(
        JsonFactory $resultJsonFactory,
        SearchEngine $searchEngine,
        RequestBuilderFactory $requestBuilderFactory,
        ScopeResolverInterface $scopeResolver,
        ProductFactory $productRepository,
        ProductIndex $productIndex,
        Context $context
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->searchEngine = $searchEngine;
        $this->requestBuilderFactory = $requestBuilderFactory;
        $this->scopeResolver = $scopeResolver;
        $this->productRepository = $productRepository;
        $this->productIndex = $productIndex;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $response = $this->resultJsonFactory->create();

        if ($this->getRequest()->getParam('q') && !empty($this->getRequest()->getParam('q'))) {
            $this->query = $this->getRequest()->getParam('q');
        } else {
            $this->status = self::STATUS_ERROR;
            $this->result[] = '<p>Please specify search term</p>';
            return $response->setData(['result' => implode('', $this->result), 'status' => $this->status]);
        }

        if ($this->getRequest()->getParam('product_id') && !empty($this->getRequest()->getParam('product_id'))) {
            $productId = $this->getRequest()->getParam('product_id');
        } else {
            $this->status = self::STATUS_ERROR;
            $this->result[] = '<p>Please specify desired product ID</p>';
            return $response->setData(['result' => implode('', $this->result), 'status' => $this->status]);
        }

        $start = microtime(true);

        $requestBuilder = $this->requestBuilderFactory->create();

        $requestBuilder->bind('search_term', $this->query);

        $requestBuilder->bindDimension('scope', $this->scopeResolver->getScope());

        $requestBuilder->setRequestName('catalogsearch_fulltext');

        $queryRequest = $requestBuilder->create();

        $collection = $this->searchEngine->search($queryRequest);

        $ids = [];
        foreach ($collection->getIterator() as $item) {
            $ids[] = $item->getId();
        }

        if (in_array($productId, $ids)) {
            $this->result[] = '<p> Product ' . $productId . ' found in ' . round(microtime(true) - $start, 4) . 'sec </p>';
        } else {
            $this->status = self::STATUS_ERROR;
            $this->getProductEntity($productId);
            $this->result[] = '<p> Product ' . $productId . ' is not found. Elapsed time :' . round(microtime(true) - $start, 4) . 'sec </p>';

            $this->isProductExist($ids);
            $this->isContainingSearchTerm();
            if ($this->proceedTest) {
                $this->result[] = '<p>Test is finished. If you didn`t solve your issue please flush all caches and run full reindex </p>';
            }
        }

        return $response->setData(['result' => implode('', $this->result), 'status' => $this->status]);
    }

    private function isProductExist($ids)
    {
        if (!$this->product->getTypeId()) {
            $this->proceedTest = false;
            return '<p> Requested product deesn`t exist </p>';
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productParents = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($this->product->getId());

        if (!empty($productParents)) {
            foreach ($productParents as $productParent) {
                if (in_array($productParent, $ids)) {
                    $this->proceedTest = false;
                    $this->status = self::STATUS_SUCCESS;
                    $this->result[] = '<p> Requested product is a child product of ' . $productParent . ' , which is found . </p>';

                    return;
                }
            }
        }
        // $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());
        // $parentIds = $product->getTypeInstance()->getParentIdsByChild($product->getId());

        if ($this->product->isDisabled()) {
            $this->proceedTest = false;
            $this->status = self::STATUS_ERROR;
            $this->result[] = '<p> Requested product is disabled </p>';
            return;
        }

        $this->result[] = '<p> Requested product visibillity is "' . Visibility::getOptionText($this->product->getVisibility()) . '", '
            . 'stock option is ' . (($this->product->isInStock()) ? "in stock" : "out of stock") . ' , '
            . 'product is ' . (($this->product->isDisabled()) ? "disabled" : "enabled") . ' , '
            . (($this->product->isAvailable()) ? "available" : "not available") . ' , '
            . 'and ' . (($this->product->getIsSalable()) ? "salable" : "not salable") . ' .</p>';

        return;
    }

    private function isContainingSearchTerm()
    {
        if (!$this->proceedTest) {
            return;
        }

        $dataIndex = '';
        $searchAttributes = [];
        $attributes = $this->productIndex->getAttributeWeights();
        foreach ($attributes as $key => $attribute) {
            $attributeData = preg_replace('!\s+!', ' ', strip_tags($this->product->getData($key)));
            $attributeData = implode(' ', explode(' ', $attributeData));

            $dataIndex .= ' ' . $attributeData;
            $searchAttributes[$key] = $attributeData;
        }
        $this->result[] = '<p> Search attributes values :</p>';
        $this->result[] = $this->buildAttributesTable($searchAttributes);

        $searhParts = explode(' ', $this->query);
        $searchedDataIndex = $dataIndex;
        foreach ($searhParts as $term) {
            $searchedDataIndex = str_replace($term, '<b>' . $term . '</b>', $searchedDataIndex);
        }

        if ($searchedDataIndex == $dataIndex) {
            $this->result[] = '<p>Search term "' . $this->query . '" didn`t match any attribute </p>';
        } else {
            $this->result[] = '<p>Search term "' . $this->query . '" has entires in data index, check the following: </p>';
            $this->result[] = '<p>' . $searchedDataIndex . '</p>';
        }

        return;
    }

    private function getProductEntity($productId)
    {
        $this->product = $this->productRepository->create()->load($productId);
    }

    private function buildAttributesTable($searchAttributes)
    {
        $result = '<table style="border:1px solid">';
        foreach ($searchAttributes as $key => $attributeValue) {
            $result .= '<tr style="border:1px solid">';
            $result .= '<td style="border:1px solid">' . $key . '</td><td style="border:1px solid">' . $attributeValue . '</td>';
            $result .= '</tr>';
        }
        $result .= '</table>';

        return $result;
    }
}
