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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


//@codingStandardsIgnoreFile
namespace Mirasvit\Email\Helper;

class Event extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Mirasvit\Email\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\CustomerFactory                          $customerFactory
     * @param \Magento\Quote\Model\QuoteFactory                                $quoteFactory
     * @param \Magento\Sales\Model\OrderFactory                                $orderFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory       $quoteCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory       $orderCollectionFactory
     * @param \Mirasvit\Email\Model\Config                                     $config
     * @param \Magento\Framework\App\Helper\Context                            $context
     * @param \Magento\Store\Model\StoreManagerInterface                       $storeManager
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Mirasvit\Email\Model\Config $config,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customerFactory = $customerFactory;
        $this->quoteFactory = $quoteFactory;
        $this->orderFactory = $orderFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    public function getRandomEventArgs()
    {
        $customerCollection = $this->customerCollectionFactory->create();
        $customerCollection->getSelect()->limit(1, rand(0, $customerCollection->getSize() - 1));
        $customer = $this->customerFactory->create()->load($customerCollection->getFirstItem()->getId());

        $quoteCollection = $this->quoteCollectionFactory->create()
            ->addFieldToFilter('items_qty', ['gt' => 0]);
        $quoteCollection->getSelect()->limit(1, rand(0, $quoteCollection->getSize() - 1));
        $quote = $this->quoteFactory->create()->setSharedStoreIds(array_keys($this->storeManager->getStores()))
            ->load($quoteCollection->getFirstItem()->getId());

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->getSelect()->limit(1, rand(0, $orderCollection->getSize() - 1));
        $order = $this->orderFactory->create()->load($orderCollection->getFirstItem()->getId());

        $testEmail = $this->config->getTestEmail();

        $store = $this->storeManager->getWebsite(true)
            ->getDefaultGroup()
            ->getDefaultStore();

        $args = [
            'customer_id'    => $customer->getId(),
            'customer_email' => $testEmail,
            'customer_name'  => $customer->getName(),
            'quote_id'       => $quote->getId(),
            'order_id'       => $order->getId(),
            'ts'             => time(),
            'store_id'       => $store->getId(),
        ];

        return $args;
    }
}
