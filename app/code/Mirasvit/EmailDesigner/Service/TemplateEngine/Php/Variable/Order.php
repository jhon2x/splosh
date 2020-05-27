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


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable;

use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;

class Order
{
    /**
     * Order constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param OrderFactory          $orderFactory
     * @param Context               $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        OrderFactory $orderFactory,
        Context $context
    ) {
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        $order = $this->orderFactory->create();

        if ($this->context->getData('order')) {
            return $this->context->getData('order');
        } elseif ($this->context->getData('order_id')) {
            $order = $this->orderFactory->create()
                ->load($this->context->getData('order_id'));
        }

        $this->context->setData('order', $order);

        return $order;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerName(\Magento\Sales\Model\Order $order = null, $default = '')
    {
        if (null === $order || $this->context->getData('customer_name')) {
            return $this->context->getData('customer_name');
        }

        $customerName = '';
        if ($order->getCustomerFirstname()) {
            $customerName = $order->getCustomerName();
        } elseif ($order->getBillingAddress()) {
            $customerName = $order->getBillingAddress()->getFirstname()
                . ' ' . $order->getBillingAddress()->getLastname();
        } elseif ($order->getShippingAddress()) {
            $customerName = $order->getShippingAddress()->getFirstname()
                . ' ' . $order->getShippingAddress()->getLastname();
        }

        return $customerName ?: $default;
    }

    /**
     * @inheritdoc
     */
    public function getRandomVariables()
    {
        $variables = [];
        $orderCollection = $this->orderFactory->create()->getCollection();
        if ($orderCollection->getSize()) {
            $orderCollection->getSelect()->limit(1, rand(0, $orderCollection->getSize() - 1));

            /** @var \Magento\Sales\Model\Order $order */
            $order = $orderCollection->getFirstItem();

            if ($order->getId()) {
                $variables['order_id'] = $order->getId();
            }
        }

        return $variables;
    }
}
