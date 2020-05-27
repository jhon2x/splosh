<?php

namespace Acidgreen\CheckoutSteps\Block;

class Checkoutstep extends \Magento\GoogleTagManager\Block\Ga
{

    protected $isSuccessPage;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection
     * @param \Magento\GoogleTagManager\Helper\Data $googleAnalyticsData
     * @param \Magento\Cookie\Helper\Cookie $cookieHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection,
        \Magento\GoogleTagManager\Helper\Data $googleAnalyticsData,
        \Magento\Cookie\Helper\Cookie $cookieHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $salesOrderCollection,
            $googleAnalyticsData,
            $cookieHelper,
            $jsonHelper,
            $data = []
        );

        $this->isSuccessPage = false;
    }

    public function getShipping()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return '';
        }
        $collection = $this->_salesOrderCollection->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orderIds]);

        $result = [];
        /** @var \Magento\Sales\Model\Order $order */
        foreach ($collection as $order) {
            $actionField['step'] = '1';
            $actionField['option'] = (string)$order->getShippingMethod();

            $products = [];
            /** @var \Magento\Sales\Model\Order\Item $item*/
            foreach ($order->getAllVisibleItems() as $item) {
                $product['id'] = $item->getSku();
                $product['name'] = $item->getName();
                $product['price'] = $item->getBasePrice();
                $product['quantity'] = $item->getQtyOrdered();
                $product['brands'] = $item->getProduct()->getAttributeText('brand');
                //$product['category'] = ''; //Not available to populate
                $products[] = $product;
            }
            $json['ecommerce']['checkout']['actionField'] = $actionField;
            $json['ecommerce']['checkout']['products'] = $products;
            $json['event'] = 'checkout';
            $result[] = 'dataLayer.push(' . $this->jsonHelper->jsonEncode($json) . ");\n";
        }
        return implode("\n", $result);
    }

    public function getPayment()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return '';
        }
        $collection = $this->_salesOrderCollection->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orderIds]);

        $result = [];
        /** @var \Magento\Sales\Model\Order $order */
        foreach ($collection as $order) {
            $actionField['step'] = '2';
            $actionField['option'] = (string)$order->getPayment()->getMethod();

            $products = [];
            /** @var \Magento\Sales\Model\Order\Item $item*/
            foreach ($order->getAllVisibleItems() as $item) {
                $product['id'] = $item->getSku();
                $product['name'] = $item->getName();
                $product['price'] = $item->getBasePrice();
                $product['quantity'] = $item->getQtyOrdered();
                $product['brands'] = $item->getProduct()->getAttributeText('brand');
                //$product['category'] = ''; //Not available to populate
                $products[] = $product;
            }
            $json['ecommerce']['checkout']['actionField'] = $actionField;
            $json['ecommerce']['checkout']['products'] = $products;
            $json['event'] = 'checkout';
            $result[] = 'dataLayer.push(' . $this->jsonHelper->jsonEncode($json) . ");\n";
        }
        return implode("\n", $result);
    }

    public function isSuccessPage() {
        return $this->isSuccessPage;
    }

    public function setSuccessPage() {
        $this->isSuccessPage = true;
    }
}
