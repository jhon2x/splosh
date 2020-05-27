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



namespace Mirasvit\Event\Service;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Event\Api\Data\EventParamsInterface;
use Mirasvit\Event\Api\Data\EventParamsInterfaceFactory;
use Mirasvit\Event\Api\Service\EventServiceInterface;

class EventService implements EventServiceInterface
{
    //    /**
    //     * @var EventParamsInterfaceFactory
    //     */
    //    private $paramsFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        //        EventParamsInterfaceFactory $paramsFactory,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager
    ) {
        //        $this->paramsFactory = $paramsFactory;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getRandomParams($storeId = null)
    {
        $params = new DataObject();

        if ($storeId === null) {
            $storeId = $this->storeManager->getWebsite(true)
                ->getDefaultGroup()
                ->getDefaultStore()
                ->getStoreId();
        }

        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection */
        $customerCollection = $this->objectManager->create('Magento\Customer\Model\ResourceModel\Customer\Collection');
        $customerCollection->addFieldToFilter('store_id', ['in' => [0, 1]]);
        $customerCollection->getSelect()->order(new \Zend_Db_Expr('RAND()'))->limit(1);

        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->objectManager->create('Magento\Customer\Model\Customer');
        $customer->load($customerCollection->getFirstItem()->getId());

        /** @var \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteCollection */
        $quoteCollection = $this->objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Collection');
        $quoteCollection->addFieldToFilter('items_qty', ['gt' => 0]);
        $quoteCollection->addFieldToFilter('store_id', $storeId);
        $quoteCollection->getSelect()->order(new \Zend_Db_Expr('RAND()'))->limit(1);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote = $quote->setSharedStoreIds(array_keys($this->storeManager->getStores()))
            ->load($quoteCollection->getFirstItem()->getId());

        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection */
        $orderCollection = $this->objectManager->create('Magento\Sales\Model\ResourceModel\Order\Collection');
        $orderCollection->addFieldToFilter('store_id', $storeId);
        $orderCollection->getSelect()->order(new \Zend_Db_Expr('RAND()'))->limit(1);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order');
        $order->load($orderCollection->getFirstItem()->getId());

        $params
            ->setCreatedAt(date('d.m.Y H:i:s'))
            ->setCustomerId($customer->getId())
            ->setCustomerName($customer->getName())
            ->setCustomerEmail($customer->getEmail())
            ->setOrderId($order->getId())
            ->setQuoteId($quote->getId())
            ->setStoreId($storeId);

        return $params;
    }
}