<?php

namespace Acidgreen\B2BCustomization\Block\Catalog\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Acidgreen\SploshBackorder\Model\Stockzone;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;

class View extends \Acidgreen\Catalog\Block\Product\View
{

    /**
     * @var \Acidgreen\B2BCustomization\Model\CartItem
     */
    protected $cartItem;

    /**
     * @var array
     */
    protected $_cartItems;

    public function __construct(
        \Acidgreen\B2BCustomization\Model\CartItem $cartItem,
        \Acidgreen\SploshBackorder\Model\Stockzone\ItemFactory $stockzoneItemFactory,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {

        $this->cartItem = $cartItem;

        parent::__construct(
            $stockzoneItemFactory,
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    /**
     * Check if product exists in cart
     * @return boolean
     */
    public function cartItemExists($productId)
    {
        return $this->cartItem->cartItemExists($productId);
    }


    /**
     * Get Qty for product being viewed
     * @return int
     */
    public function getCartItemProductQty($productId)
    {
        return $this->cartItem->getCartItemProductQty($productId);
    }
}
