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

use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Helper\Stock as StockHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\UrlInterface;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Theme\Model\View\Design;
use Mirasvit\SearchAutocomplete\Index\AbstractIndex;
use Mirasvit\SearchAutocomplete\Model\Config;
use Mirasvit\SearchAutocomplete\Model\ResourceModel\Catalog\ProductFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends AbstractIndex
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var ReviewRenderer
     */
    protected $reviewRenderer;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var CatalogHelper
     */
    protected $catalogHelper;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var design
     */
    protected $design;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @var StockRegistryInterface
     */
    private $stock;

    /**
     * @var StockHelper
     */
    private $stockHelper;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $cart;

    /**
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    private $productBlock;

    public function __construct(
        TaxConfig $taxConfig,
        Config $config,
        ReviewFactory $reviewFactory,
        ReviewRenderer $reviewRenderer,
        ImageHelper $imageHelper,
        CatalogHelper $catalogHelper,
        PricingHelper $pricingHelper,
        RequestInterface $request,
        Design $design,
        StockRegistryInterface $stock,
        StockHelper $stockHelper,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->config         = $config;
        $this->reviewFactory  = $reviewFactory;
        $this->reviewRenderer = $reviewRenderer;
        $this->imageHelper    = $imageHelper;
        $this->catalogHelper  = $catalogHelper;
        $this->pricingHelper  = $pricingHelper;
        $this->request        = $request;
        $this->taxConfig      = $taxConfig;
        $this->design         = $design;
        $this->stock          = $stock;
        $this->stockHelper    = $stockHelper;
        $this->productFactory = $productFactory;
        $this->storeManager   = $storeManager;

        $this->cart = [
            'visible' => $this->config->isShowCartButton(),
            'label'   => __('Add to Cart')->render(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items      = [];
        $categoryId = intval($this->request->getParam('cat'));
        $storeId    = intval($this->request->getParam('store_id'));

        $collection = $this->getCollection();

        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('short_description')
            ->addAttributeToSelect('description');

        $this->collection->getSelect()->order('score desc');

        if (!$this->config->isOutOfStockAllowed()) {
            $this->stockHelper->addInStockFilterToCollection($this->collection);
        }

        if ($categoryId) {
            $om       = ObjectManager::getInstance();
            $category = $om->create('Magento\Catalog\Model\Category')->load($categoryId);
            $collection->addCategoryFilter($category);
        }

        if ($this->config->isShowRating()) {
            $this->reviewFactory->create()->appendSummary($collection);
        }
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product) {
            $map = $this->mapProduct($product, $storeId);
            if ($map) {
                $items[] = $map;
            }
        }

        return $items;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param int                            $storeId
     *
     * @return array
     * @SuppressWarnings(PHPMD)
     */
    public function mapProduct($product, $storeId = 1)
    {
        $item = [
            'name'        => $product->getName(),
            'url'         => $product->getProductUrl(),
            'sku'         => $this->getSku($product),
            'description' => $this->getDescription($product),
            'image'       => null,
            'price'       => $this->getPrice($product, $storeId),
            'rating'      => $this->getRating($product, $storeId),
            'cart'        => $this->getCart($product),
            'optimize'    => false,
        ];

        if ($this->config->isShowImage()) {
            $image = false;

            $image = $this->imageHelper->init($product, 'product_page_image_small')
                ->setImageFile($product->getFile())
                ->resize(65 * 2, 80 * 2)
                ->getUrl();

            if (strpos($image, '/.') !== false) {
                // wrong url was generated (image doesn't present in file system)
                $image = false;
            }

            if (!$image) {
                $image = $this->getImagePlaceholder();
            }

            $item['image'] = $image;
        }

        return $item;
    }

    /**
     * @param array                                         $documents
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     *
     * @return array
     */
    public function map($documents, $dimensions)
    {
        if (!$this->config->isFastMode() || count($documents) === 0) {
            return $documents;
        }

        $dimension = current($dimensions);
        $storeId   = $dimension->getValue();

        $productIds = array_keys($documents);

        $collection = ObjectManager::getInstance()
            ->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $collection->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->addAttributeToSelect('description')
            ->addFinalPrice()
            ->addAttributeToSelect('special_price')
            ->addAttributeToSelect('special_from_date')
            ->addAttributeToSelect('special_to_date');
        $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);
        $collection->load();

        $sitemapProducts = [];
        /** @var Mirasvit\SearchAutocomplete\Model\ResourceModel\Catalog\Product */
        $sitemapProduct = $this->productFactory->create();
        $sitemapProduct->setProductIds($productIds);
        try {
            $emulation = ObjectManager::getInstance()->get('Magento\Store\Model\App\Emulation');
            $emulation->startEnvironmentEmulation($storeId, 'frontend', true);
            $state = ObjectManager::getInstance()->get('Magento\Framework\App\State');
            $state->emulateAreaCode('frontend', function (&$sitemapProducts, $sitemapProduct, $storeId) {
                $sitemapProducts = $sitemapProduct->getCollection($storeId);
            }, [&$sitemapProducts, $sitemapProduct, $storeId]);
        } catch (\Exception $e) {
        } finally {
            $emulation->stopEnvironmentEmulation();
        }

        foreach ($collection as $product) {
            $entityId = $product->getId();
            $item     = $sitemapProducts[$entityId];

            $image = null;
            if ($this->config->isShowImage()) {
                if (!empty($item['images'])) {
                    $image = $item['images']->getThumbnail();
                } else {
                    $image = $this->getImagePlaceholder();
                }
            }

            $map = [
                'name'        => $item['name'],
                'url'         => $this->getUrl($item['url'], $storeId),
                'sku'         => ($this->config->isShowSku() ? $item['sku'] : null),
                'description' => $this->getDescription($product),
                'image'       => $image,
                'price'       => $this->getPrice($product, $storeId),
                'rating'      => $this->getRating($product, $storeId),
                'optimize'    => false,
            ];

            $documents[$entityId]['autocomplete'] = $map;
        }

        return $documents;
    }

    private function getImagePlaceholder()
    {
        $this->design->setDesignTheme('Magento/backend', 'adminhtml');

        return $this->imageHelper->getDefaultPlaceholderUrl('thumbnail');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return null|string
     */
    private function getDescription(\Magento\Catalog\Model\Product $product)
    {
        if ($this->config->isShowShortDescription()) {
            return html_entity_decode(
                strip_tags($product->getDataUsingMethod('description'))
            );
        }

        return null;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return null|string
     */
    private function getSku(\Magento\Catalog\Model\Product $product)
    {
        if ($this->config->isShowSku()) {
            return html_entity_decode(strip_tags($product->getDataUsingMethod('sku')));
        }

        return null;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param int                            $storeId
     *
     * @return null|float|string
     */
    private function getPrice(\Magento\Catalog\Model\Product $product, $storeId = 1)
    {
        if (!$this->config->isShowPrice()) {
            return null;
        }

        $price = null;
        try {
            $emulation = ObjectManager::getInstance()->get('Magento\Store\Model\App\Emulation');
            $emulation->startEnvironmentEmulation($storeId, 'frontend', true);

            $price = $product->getMinimalPrice();
            if ($price == 0 && $product->getFinalPrice() > 0) {
                $price = $product->getFinalPrice();
            } else {
                $price = $product->getMinPrice();
            }

            $includingTax = $this->taxConfig->getPriceDisplayType() !== TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX;

            $price = $this->catalogHelper->getTaxPrice($product, $price, $includingTax);
            $price = $this->pricingHelper->currency($price, false, false);
        } catch (\Exception $e) {
        } finally {
            $emulation->stopEnvironmentEmulation();
        }

        return $price;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    private function getCart(\Magento\Catalog\Model\Product $product)
    {
        if ($this->productBlock === null) {
            $this->productBlock = ObjectManager::getInstance()
                ->create('Magento\Catalog\Block\Product\ListProduct');
        }

        $cart           = $this->cart;
        $cart['params'] = $this->productBlock->getAddToCartPostParams($product);

        return $cart;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param int                            $storeId
     *
     * @return string | null
     */
    private function getRating(\Magento\Catalog\Model\Product $product, $storeId = 1)
    {
        if (!$this->config->isShowRating() || !$product->getRatingSummary()) {
            return null;
        }

        $rating = null;
        try {
            /** @var \Magento\Store\Model\App\Emulation $emulation */
            $emulation = ObjectManager::getInstance()->get('Magento\Store\Model\App\Emulation');
            $emulation->startEnvironmentEmulation($storeId, 'frontend', true);

            /** @var \Magento\Framework\App\State $state */
            $state = ObjectManager::getInstance()->get('Magento\Framework\App\State');

            $state->emulateAreaCode('frontend', function (&$rating, $product) {
                $rating = $this->reviewRenderer
                    ->getReviewsSummaryHtml($product, ReviewRendererInterface::SHORT_VIEW);
            }, [&$rating, $product]);
        } catch (\Exception $e) {
        } finally {
            $emulation->stopEnvironmentEmulation();
        }

        return $rating;
    }

    /**
     * @param string  $url
     * @param integer $storeId
     * @param string  $type
     *
     * @return string
     */
    private function getUrl($url, $storeId, $type = UrlInterface::URL_TYPE_LINK)
    {
        return $this->getStoreBaseUrl($storeId, $type) . ltrim($url, '/');
    }

    /**
     * @param integer $storeId
     * @param string  $type
     *
     * @return string
     */
    private function getStoreBaseUrl($storeId, $type = UrlInterface::URL_TYPE_LINK)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store    = $this->storeManager->getStore($storeId);
        $isSecure = $store->isUrlSecure();

        return rtrim($store->getBaseUrl($type, $isSecure), '/') . '/';
    }
}
