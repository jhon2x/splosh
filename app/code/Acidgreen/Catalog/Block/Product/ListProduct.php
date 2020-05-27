<?php

namespace Acidgreen\Catalog\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Catalog\Block\Product\ListProduct as CoreListProductBlock;
use Psr\Log\LoggerInterface;

class ListProduct extends CoreListProductBlock
{
	/**
	 * @param Context $context
	 * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
	 * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
	 * @param CategoryRepositoryInterface $categoryRepository
	 * @param \Magento\Framework\Url\Helper\Data $urlHelper
	 * @param array $data
	 */
	public function __construct(
		\Magento\Catalog\Block\Product\Context $context,
		\Magento\Framework\Data\Helper\PostHelper $postDataHelper,
		\Magento\Catalog\Model\Layer\Resolver $layerResolver,
		CategoryRepositoryInterface $categoryRepository,
		\Magento\Framework\Url\Helper\Data $urlHelper,
		array $data = []
	) {
		parent::__construct(
			$context,
			$postDataHelper,
			$layerResolver,
			$categoryRepository,
			$urlHelper,
			$data
		);
	}
	/**
	 * Overwrite for filtering product collection
	 *
	 * @return AbstractCollection
	 */
	protected function _getProductCollection()
	{
		if ($this->_productCollection === null) {
			$layer = $this->getLayer();
			/* @var $layer \Magento\Catalog\Model\Layer */
			if ($this->getShowRootCategory()) {
				$this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
			}

			// if this is a product view page
			if ($this->_coreRegistry->registry('product')) {
				// get collection of categories this product is associated with
				$categories = $this->_coreRegistry->registry('product')
					->getCategoryCollection()->setPage(1, 1)
					->load();
				// if the product is associated with any category
				if ($categories->count()) {
					// show products from this category
					$this->setCategoryId(current($categories->getIterator()));
				}
			}

			$origCategory = null;
			if ($this->getCategoryId()) {
				try {
					$category = $this->categoryRepository->get($this->getCategoryId());
				} catch (NoSuchEntityException $e) {
					$category = null;
				}

				if ($category) {
					$origCategory = $layer->getCurrentCategory();
					$layer->setCurrentCategory($category);
				}
			}
			$this->_productCollection = $layer->getProductCollection();
			
			$this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

			if ($origCategory) {
				$layer->setCurrentCategory($origCategory);
			}
		}

		/**
	     * Removed the filtering by custom attribute here (Got problem with Catalog Search)
	     * Still need to add the getSize here to get the correct count of filtered collection
	     */
		$this->_productCollection->getSize();

		return $this->_productCollection;
	}
}
