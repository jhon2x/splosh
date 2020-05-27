<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acidgreen\Checkout\Block\Onepage;

/**
 * One page checkout success page
 */
class Success extends \Magento\Checkout\Block\Onepage\Success
{

    protected $storeManager;

    const CONFIG_PATH_ENABLED           = 'google/customer_reviews/enabled';
    const CONFIG_PATH_API_URL           = 'google/customer_reviews/api_url';
    const CONFIG_PATH_MERCHANT_ID       = 'google/customer_reviews/merchant_id';

    /**
     * Prepares block data
     * Override - added Order Amount data
     *
     * @return void
     */
    protected function prepareBlockData()
    {
        $order = $this->_checkoutSession->getLastRealOrder();

        $this->addData(
            [
                'is_order_visible' => $this->isVisible($order),
                'view_order_url' => $this->getUrl(
                    'sales/order/view/',
                    ['order_id' => $order->getEntityId()]
                ),
                'print_url' => $this->getUrl(
                    'sales/order/print',
                    ['order_id' => $order->getEntityId()]
                ),
                'can_print_order' => $this->isVisible($order),
                'can_view_order'  => $this->canViewOrder($order),
                'order_grand_total' => $order->getGrandTotal(),
                'order_id'  => $order->getIncrementId(),
                'email'  => $order->getCustomerEmail(),
                'delivery_country' => $this->getDeliveryCountry($order),
                'estimated_delivery_date' => $this->getEstimatedDeliveryDate($order),
                'order_products' => $this->getOrderProducts($order),
                'api_url' => $this->getApiUrl(),
                'merchant_id' => $this->getMerchantId(),
            ]
        );
    }

    protected function getDeliveryCountry($order)
    {
        return 'AU';
    }

    protected function getEstimatedDeliveryDate($order)
    {
        $estimatedDeliveryDate = new \DateTime($order->getCreatedAt());
        $estimatedDeliveryDate->modify('+7 day');

        return $estimatedDeliveryDate->format('Y-m-d');
    }

    protected function getOrderProducts($order) {
        $items = $order->getItemsCollection();
        $products = [];

        foreach ($items as $item) {
            $products[] = ['gtin' => $item->getSku()];
        }

        return json_encode($products);
    }

    public function isCustomerReviewsEnabled()
    {
        return $this->_getConfigData(self::CONFIG_PATH_ENABLED);
    }

    public function getApiUrl()
    {
        return $this->_getConfigData(self::CONFIG_PATH_API_URL);
    }

    public function getMerchantId()
    {
        return $this->_getConfigData(self::CONFIG_PATH_MERCHANT_ID);
    }

    protected function _getConfigData($path)
    {
        $storeManager = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');
        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $storeManager->getWebsite()->getId()
        );

    }
}
