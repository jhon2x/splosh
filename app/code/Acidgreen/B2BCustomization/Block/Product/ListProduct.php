<?php

namespace Acidgreen\B2BCustomization\Block\Product;

class ListProduct extends \Acidgreen\SploshBackorder\Block\Product\ListProduct
{

    /**
     * @var \Acidgreen\B2BCustomization\Model\CartItem
     */
    protected $cartItem;

    public function __construct(
		\Acidgreen\SploshBackorder\Model\StockzoneFactory $stockzoneFactory,
		\Acidgreen\SploshBackorder\Model\Stockzone\ItemFactory $stockzoneItemFactory,
		\Acidgreen\Checkout\Helper\Cart $agCheckoutHelper,
		\Acidgreen\CustomerRestrictions\Helper\Restrictions $customerRestrictions,
		\Magento\Catalog\Block\Product\Context $context,
		\Magento\Framework\Data\Helper\PostHelper $postDataHelper,
		\Magento\Catalog\Model\Layer\Resolver $layerResolver,
		\Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
		\Magento\Framework\Url\Helper\Data $urlHelper,
        \Acidgreen\B2BCustomization\Model\CartItem $cartItem,
		array $data = []
    ) {
        $this->cartItem = $cartItem;
        parent::__construct(
            $stockzoneFactory,
            $stockzoneItemFactory,
            $agCheckoutHelper,
            $customerRestrictions,
			$context,
			$postDataHelper,
			$layerResolver,
			$categoryRepository,
			$urlHelper,
			$data
        );
    }

    public function getCartItemsByProductId()
    {
        return $this->cartItem->getCartItemsByProductId();
    }
}
