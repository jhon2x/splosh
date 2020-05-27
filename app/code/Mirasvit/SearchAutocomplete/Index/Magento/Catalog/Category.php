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

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SearchAutocomplete\Index\AbstractIndex;

class Category extends AbstractIndex
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->storeManager       = $storeManager;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($this->getCollection() as $category) {
            $items[] = $this->mapCategory($category);
        }

        return $items;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     */
    public function mapCategory($category)
    {
        return [
            'name' => $this->getFullPath($category),
            'url'  => $category->getUrl(),
        ];
    }

    /**
     * List of parent categories
     * @param CategoryInterface $category
     * @return string
     */
    public function getFullPath(CategoryInterface $category)
    {
        $store  = $this->storeManager->getStore();
        $rootId = $store->getRootCategoryId();

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

        return implode('<i>›</i>', $result);
    }

    public function map($data)
    {
        foreach ($data as $entityId => $itm) {
            $om     = ObjectManager::getInstance();
            $entity = $om->create('Magento\Catalog\Model\Category')->load($entityId);

            $map                             = $this->mapCategory($entity);
            $data[$entityId]['autocomplete'] = $map;
        }

        return $data;
    }
}
