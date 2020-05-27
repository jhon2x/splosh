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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable;

use Magento\Catalog\Model\ProductFactory;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory as WishlistCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Mirasvit\EmailDesigner\Api\Service\VariableInterface;

class Wishlist extends AbstractVariable implements VariableInterface
{
    protected $supportedTypes = ['Magento\Wishlist\Model\Wishlist'];

    /**
     * @var CollectionFactory
     */
    private $wishlistItemCollectionFactory;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * Constructor
     *
     * @param ProductFactory             $productFactory
     * @param CollectionFactory          $wishlistItemCollectionFactory
     * @param StoreManagerInterface      $storeManager
     * @param WishlistCollectionFactory  $wishlistCollectionFactory
     * @param WishlistFactory            $wishlistFactory
     * @param Context                    $context
     */
    public function __construct(
        ProductFactory $productFactory,
        CollectionFactory $wishlistItemCollectionFactory,
        StoreManagerInterface $storeManager,
        WishlistCollectionFactory $wishlistCollectionFactory,
        WishlistFactory $wishlistFactory,
        Context $context
    ) {
        parent::__construct();

        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->storeManager = $storeManager;
        $this->wishlistFactory = $wishlistFactory;
        $this->context = $context;
        $this->wishlistItemCollectionFactory = $wishlistItemCollectionFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * Wishlist model
     *
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function getWishlist()
    {
        if ($this->context->getData('wishlist')) {
            return $this->context->getData('wishlist');
        }

        $wishlist = $this->wishlistFactory->create();
        if ($this->context->getData('wishlist_id')) {
            $wishlist = $wishlist->setSharedStoreIds(array_keys($this->storeManager->getStores()))
                ->load($this->context->getData('wishlist_id'));
        }

        $this->context->setData('wishlist', $wishlist);

        return $wishlist;
    }

    /**
     * Get item collection related with wishlist
     *
     * @return \Magento\Wishlist\Model\ResourceModel\Item\Collection||\Magento\Wishlist\Model\Item[]
     */
    public function getItemCollection()
    {
        if ($this->context->getData('wishlist_item_collection')) {
            return $this->context->getData('wishlist_item_collection');
        }

        $wishlist = $this->getWishlist();
        $itemCollection = $this->wishlistItemCollectionFactory->create()
            ->addWishlistFilter($wishlist);

        $this->context->setData('wishlist_item_collection', $itemCollection);

        return $itemCollection;
    }

    /**
     * Get product added to wishlist or last added product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if ($this->context->getData('wishlist_product')) {
            return $this->context->getData('wishlist_product');
        }

        $product = $this->productFactory->create()->setStoreId($this->context->getData('store_id'));
        if ($this->context->getData('product_id')) {
            $product = $product->load($this->context->getData('product_id'));
        } elseif ($wishlist = $this->getWishlist()) {
            $productId = $wishlist->getItemCollection()
                ->setOrder('added_at', 'desc')
                ->getFirstItem()->getProductId();
            $product = $product->load($productId);
        }

        $this->context->setData('wishlist_product', $product);

        return $product;
    }
}
