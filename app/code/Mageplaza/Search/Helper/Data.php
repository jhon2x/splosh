<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Search
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Search\Helper;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\Search\Model\Product\Url;

/**
 * Search helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpsearch';

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $_customerGroupFactory;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        CollectionFactory $customerGroupCollectionFactory,
        Escaper $escaper,
        PricingHelper $priceHelper,
        Visibility $catalogProductVisibility,
        Config $catalogConfig,
        Session $customerSession,
        FormatInterface $localeFormat,
        CategoryFactory $categoryFactory
    )
    {
        $this->_customerGroupFactory = $customerGroupCollectionFactory;
        $this->_escaper              = $escaper;
        $this->_priceHelper          = $priceHelper;
        $this->productVisibility     = $catalogProductVisibility;
        $this->catalogConfig         = $catalogConfig;
        $this->_customerSession      = $customerSession;
        $this->localeFormat          = $localeFormat;
        $this->categoryFactory       = $categoryFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->getConfigGeneral('enabled', $storeId) && $this->isModuleOutputEnabled();
    }

    /**
     * @param string $code
     * @param null $storeId
     * @return mixed
     */
    public function getConfigGeneral($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/general' . $code, $storeId);
    }

    /**
     * @return \Mageplaza\Search\Helper\Media
     */
    public function getMediaHelper()
    {
        return $this->objectManager->get(Media::class);
    }

    /**
     * @param null $store
     * @return string
     */
    public function getSearchBy($store = null)
    {
        $searchBy = $this->getConfigGeneral('search_by', $store);

        return self::jsonEncode(explode(',', $searchBy));
    }

    /**
     * @param null $store
     * @return string
     */
    public function getDisplay($store = null)
    {
        $searchBy = $this->getConfigGeneral('display', $store);

        return self::jsonEncode(explode(',', $searchBy));
    }

    /**
     * Create json file to contain product data
     */
    public function createJsonFile()
    {
        $errors         = [];
        $customerGroups = $this->_customerGroupFactory->create();
        foreach ($this->storeManager->getStores() as $store) {
            foreach ($customerGroups as $group) {
                try {
                    $this->createJsonFileForStore($store, $group->getId());
                } catch (\Exception $e) {
                    $errors[] = __('Cannot generate data for store %1 and customer group %2, %3', $store->getCode(), $group->getCode(), $e->getMessage());
                }
            }
        }

        return $errors;
    }

    /**
     * @param $store
     * @param $group
     * @return $this
     */
    public function createJsonFileForStore($store, $group)
    {
        if(!$this->isEnabled($store->getId())){
            return $this;
        }

        $productList = [];

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->objectManager->create(Collection::class);
        $collection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($store)
            ->addPriceData($group)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInSearchIds());

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product) {
            $productList[] = [
                'value' => $product->getName(),
                'c'     => $product->getCategoryIds(), //categoryIds
                'd'     => $this->getProductDescription($product, $store), //short description
                'p'     => $this->_priceHelper->currencyByStore($product->getFinalPrice(), $store, false, false), //price
                'i'     => $this->getMediaHelper()->getProductImage($product),//image
                'u'     => $this->getProductUrl($product) //product url
            ];
        }

        $this->getMediaHelper()->createJsFile(
            $this->getJsFilePath($group, $store),
            'var mageplazaSearchProducts = ' . self::jsonEncode($productList)
        );

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool|string
     */
    protected function getProductUrl($product)
    {
        $productUrl  = $product->getProductUrl();
        $requestPath = $product->getRequestPath();
        if (!$requestPath) {
            $pos = strpos($productUrl, 'catalog/product/view');
            if ($pos !== false) {
                $productUrl = substr($productUrl, $pos + 20);
            }
        } else {
            $productUrl = $requestPath;
        }

        return $productUrl;
    }

    /**
     * @param $product
     * @param $store
     * @return array|bool|string
     */
    protected function getProductDescription($product, $store)
    {
        $attributeHtml = strip_tags($product->getShortDescription());
        $attributeHtml = $this->_escaper->escapeHtml($attributeHtml);
        if ($limitDesLetter = (int)$this->getConfigGeneral('max_letter_numbers', $store->getId())) {
            $attributeHtml = substr($attributeHtml, 0, $limitDesLetter);
        }

        return $attributeHtml;
    }

    /**
     * @param int $customerGroupId
     * @param \Magento\Store\Model\Store $store
     * @return string
     */
    public function getJsFilePath($customerGroupId, $store)
    {
        return Media::TEMPLATE_MEDIA_PATH . '/' . $store->getCode() . '_' . $customerGroupId . '.js';
    }

    /**
     * @return string
     */
    public function getJsFileUrl()
    {
        $customerGroupId = $this->_customerSession->getCustomerGroupId();

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        $mediaDirectory = $this->getMediaHelper()->getMediaDirectory();
        $filePath       = $this->getJsFilePath($customerGroupId, $store);
        if (!$mediaDirectory->isFile($filePath)) {
            $this->createJsonFileForStore($store, $customerGroupId);
        }

        return $this->getMediaHelper()->getMediaUrl($filePath);
    }

    /**
     * @return array
     */
    public function getCategoryTree()
    {
        $categoriesOptions = [0 => __('All Categories')];

        $maxLevel   = max(0, (int)$this->getConfigGeneral('category/max_depth')) ?: 2;
        $parent     = $this->storeManager->getStore()->getRootCategoryId();
        $categories = $this->categoryFactory->create()
            ->getCategories($parent, 1, false, true);
        foreach ($categories as $category) {
            $this->getCategoryOptions($category, $categoriesOptions, $maxLevel);
        }

        return $categoriesOptions;
    }

    /**
     * @param $category
     * @param $options
     * @param $level
     * @param string $htmlPrefix
     * @return $this
     */
    protected function getCategoryOptions($category, &$options, $level, $htmlPrefix = '')
    {
        if ($level <= 0) {
            return $this;
        }
        $level--;

        $options[$category->getId()] = $htmlPrefix . $category->getName();

        $htmlPrefix .= '- ';
        foreach ($this->getChildCategories($category) as $childCategory) {
            $this->getCategoryOptions($childCategory, $options, $level, $htmlPrefix);
        }

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     */
    public function getChildCategories($category)
    {
        if ($category->getUseFlatResource()) {
            return $category->getChildrenNodes();
        }

        return $category->getChildrenCategories();
    }

    /**
     * @return string
     */
    public function getPriceFormat()
    {
        return self::jsonEncode($this->localeFormat->getPriceFormat());
    }
}