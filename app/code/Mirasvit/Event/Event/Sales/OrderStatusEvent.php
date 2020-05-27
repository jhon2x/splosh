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
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Event\Sales;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\AddressShippingData;
use Mirasvit\Event\EventData\CustomerData;
use Mirasvit\Event\EventData\OrderData;
use Mirasvit\Event\EventData\StoreData;

class OrderStatusEvent extends ObservableEvent
{
    const IDENTIFIER = 'order_status|';

    /**
     * @var OrderConfig
     */
    private $orderConfig;

    public function __construct(
        OrderConfig $orderConfig,
        Context $context
    ) {
        $this->orderConfig = $orderConfig;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        $result                   = [];
        $result[self::IDENTIFIER] = __('Sales / Order status was changed');

        foreach ($this->orderConfig->getStatuses() as $code => $name) {
            $result[self::IDENTIFIER . $code] = __("Sales / Order obtained '%1' status", $name);
        }

        // additional status (can be added by Amasty Order Status)
        foreach (array_keys($this->orderConfig->getStates()) as $state) {
            foreach ($this->orderConfig->getStateStatuses($state) as $code => $name) {
                $result[self::IDENTIFIER . $code] = __("Sales / Order obtained '%1' status", $name);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(StoreData::class),
            $this->context->get(OrderData::class),
            $this->context->get(CustomerData::class),
            $this->context->get(AddressShippingData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        $params = $this->expand($params);

        /** @var OrderData $order */
        $order = $params[OrderData::IDENTIFIER];

        return __('Order #%1. Status was changed to %2.', $order->getIncrementId(), $order->getStatusLabel());
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        /** @var OrderData $order */
        $order = $this->context->create(OrderData::class)->load($params[OrderData::ID]);
        /** @var CustomerData $customer */
        $customer = $this->context->create(CustomerData::class)->load($params[CustomerData::ID]);
        $store    = $this->context->create(StoreData::class)->load($params[self::PARAM_STORE_ID]);

        $params[OrderData::IDENTIFIER]           = $order;
        $params[CustomerData::IDENTIFIER]        = $customer;
        $params[StoreData::IDENTIFIER]           = $store;
        $params[AddressShippingData::IDENTIFIER] = $order->getShippingAddress()
            ? $order->getShippingAddress()
            : $customer->getPrimaryShippingAddress();

        return $params;
    }

    /**
     * @param Order $order
     *
     * @return void
     */
    protected function register(Order $order)
    {
        $params = [
            self::PARAM_EXPIRE_AFTER   => 30 * 24 * 60 * 60,
            self::PARAM_STORE_ID       => $order->getStoreId(),
            OrderData::ID              => $order->getId(),
            CustomerData::ID           => $order->getCustomerId(),
            self::PARAM_CUSTOMER_EMAIL => $order->getCustomerEmail(),
            self::PARAM_CUSTOMER_NAME  => $this->getCustomerName($order),
        ];

        $this->context->eventRepository->register(
            self::IDENTIFIER,
            [$order->getId(), $order->getStatus()],
            $params
        );

        $this->context->eventRepository->register(
            self::IDENTIFIER . $order->getStatus(),
            [$order->getId()],
            $params
        );
    }

    /**
     * Get customer name associated with order.
     *
     * @param Order $order
     *
     * @return string
     */
    private function getCustomerName(Order $order)
    {
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

        return $customerName;
    }
}