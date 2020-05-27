<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\Profiler;
use MageWorx\ShippingRules\Model\Config\Source\Shipping\Methods as ShippingMethodsSource;

/**
 * Class CollectValidMethods
 */
class CollectValidMethods
{
    /**
     * @var array
     */
    private $availableShippingMethodsOverall = [];

    /**
     * @var ProductResourceModel
     */
    private $productResource;

    /**
     * @var ShippingMethodsSource
     */
    private $shippingMethodsSource;

    /**
     * @var bool
     */
    private $isCollected = false;

    /**
     * CollectValidMethods constructor.
     *
     * @param ProductResourceModel $productResource
     * @param ShippingMethodsSource $shippingMethodsSource
     */
    public function __construct(
        ProductResourceModel $productResource,
        ShippingMethodsSource $shippingMethodsSource
    ) {
        $this->productResource       = $productResource;
        $this->shippingMethodsSource = $shippingMethodsSource;
    }

    /**
     * @param \Magento\Shipping\Model\Shipping $subject
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return array
     */
    public function beforeCollectRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
        /** @var \Magento\Quote\Model\Quote\Item[] $allItems */
        $allItems = $request->getAllItems();
        if (empty($allItems)) {
            return [$request];
        }

        $this->collectAvailableShippingMethodsForItems($allItems);

        return [$request];
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @return array
     */
    public function collectAvailableShippingMethodsForItems(array $items)
    {
        Profiler::start('collect_available_shipping_methods_for_items');

        // By default all shipping methods is available.
        // Next we will remove a methods which was not set in the product attribute as an available
        // (one-by-one for each product). Attribute name is: 'available_shipping_methods'
        $availableShippingMethods = $this->shippingMethodsSource->toArray();

        $productsShippingMethods = [];

        foreach ($items as $quoteItem) {
            Profiler::start('check_available_shipping_methods_in_item_' . $quoteItem->getId());
            $product = $quoteItem->getProduct();
            $this->productResource->load($product, $product->getId(), ['available_shipping_methods']);
            $productAvailableShippingMethods = $product->getData('available_shipping_methods');

            if (empty($productAvailableShippingMethods)) {
                continue; // No one method selected means no restriction!
            } elseif (!is_array($productAvailableShippingMethods)) {
                $productAvailableShippingMethods = \explode(',', $productAvailableShippingMethods);
            }

            $productsShippingMethods[$product->getId()] = $productAvailableShippingMethods;
            Profiler::stop('check_available_shipping_methods_in_item_' . $quoteItem->getId());
        }

        if (!empty($productsShippingMethods)) {
            $availableShippingMethods = \array_intersect($availableShippingMethods, ...$productsShippingMethods);
        }

        $this->availableShippingMethodsOverall = \array_unique($availableShippingMethods);
        $this->isCollected                     = true;

        Profiler::stop('collect_available_shipping_methods_for_items');

        return $this->availableShippingMethodsOverall;
    }

    /**
     * Returns array of all available shipping methods calculated from products (from request)
     *
     * @return array
     */
    public function getAvailableShippingMethods()
    {
        return $this->availableShippingMethodsOverall;
    }

    /**
     * Check is available shipping methods was collected
     *
     * @return bool
     */
    public function getIsCollected()
    {
        return $this->isCollected;
    }
}