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



namespace Mirasvit\SearchAutocomplete\Index\Magento\Catalog;

use Magento\Catalog\Api\Data\CategoryInterface;
use Mirasvit\SearchAutocomplete\Index\AbstractIndex;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Framework\UrlFactory;
use Mirasvit\SearchAutocomplete\Api\Service\CategoryProductInterface;

/**
 * @todo rewrite
 */
class CategoryProduct extends AbstractIndex
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $searchHelper;

    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @var $categoryProductInterface ;
     */
    protected $categoryProduct;

    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        SearchHelper $searchHelper,
        UrlFactory $urlFactory,
        CategoryProductInterface $categoryProduct
    ) {
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->searchHelper = $searchHelper;
        $this->urlFactory = $urlFactory;
        $this->categoryProduct = $categoryProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];
        $collection = $this->categoryProduct->getCollection($this->index);

        $store = $this->storeManager->getStore();
        $rootId = $store->getRootCategoryId();

        foreach ($collection as $category) {
            $url = $this->urlFactory->create()
                ->setQueryParam('q', $this->searchHelper->getEscapedQueryText())
                ->setQueryParam('cat', $category->getId())
                ->getUrl('catalogsearch/result');

            $items[] = [
                'name' => $this->getFullPath($category, $rootId),
                'url'  => $url,
            ];

            if (count($items) >= $this->index->getLimit()) {
                break;
            }
        }
        return $items;
    }

    /**
     * List of parent categories
     *
     * @param CategoryInterface $category
     * @param Variable $rootId current store root category Id
     *
     * @return string
     */
    public function getFullPath(CategoryInterface $category, $rootId)
    {
        $result = [
            $category->getName(),
        ];

        do {
            if (!$category->getParentId()) {
                break;
            }
            $category = $this->categoryRepository->get($category->getParentId());

            if (!$category->getIsActive() && $category->getId() != $rootId) {
                break;
            }

            if ($category->getId() != $rootId) {
                $result[] = $category->getName();
            }
        } while ($category->getId() != $rootId);

        $result = array_reverse($result);

        return $this->searchHelper->getEscapedQueryText() . __(' in ') . implode(' > ', $result);
    }
}