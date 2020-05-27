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



namespace Mirasvit\Email\Helper;

use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Sales\Model\Order;

/**
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 */
class Frontend extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Mirasvit\Email\Model\QueueFactory
     */
    protected $queueFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    /**
     * @param \Mirasvit\Email\Model\QueueFactory              $queueFactory
     * @param \Magento\Customer\Model\CustomerFactory         $customerFactory
     * @param \Magento\Sales\Model\OrderFactory               $orderFactory
     * @param \Magento\Quote\Model\QuoteFactory               $quoteFactory
     * @param \Magento\Checkout\Model\Cart                    $cart
     * @param \Magento\Checkout\Model\Session                 $session
     * @param \Mirasvit\Email\Helper\Quote                    $quoteHelper
     * @param \Magento\Store\Model\StoreManagerInterface      $storeManager
     * @param \Magento\Customer\Model\Session                 $customerSession
     * @param \Magento\Framework\Message\ManagerInterface     $messageManager
     * @param \Magento\Framework\App\Helper\Context           $context
     */
    public function __construct(
        \Mirasvit\Email\Model\QueueFactory $queueFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $session,
        \Mirasvit\Email\Helper\Quote $quoteHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->queueFactory    = $queueFactory;
        $this->customerFactory = $customerFactory;
        $this->orderFactory    = $orderFactory;
        $this->quoteFactory    = $quoteFactory;
        $this->cart            = $cart;
        $this->session         = $session;
        $this->quoteHelper     = $quoteHelper;
        $this->storeManager    = $storeManager;
        $this->customerSession = $customerSession;
        $this->messageManager  = $messageManager;
        $this->context         = $context;

        parent::__construct($context);
    }

    /**
     * @param string $hash
     * @return \Mirasvit\Email\Model\Queue|false
     */
    public function getQueue($hash)
    {
        $queue = $this->queueFactory->create()->load($hash);

        if ($queue->getId()) {
            return $queue;
        }

        return false;
    }

    public function loginCustomerByQueueHash($hash)
    {
        $queue = $this->getQueue($hash);

        if ($queue && ($customerId = $queue->getArg('customer_id'))) {
            $customer = $this->customerFactory->create()->load($customerId);
            if (!$customer->getId()) {
                return false;
            }

            $session = $this->customerSession;
            if ($session->isLoggedIn() && $customer->getId() != $session->getCustomerId()) {
                $session->logout();
                $session->setCustomerAsLoggedIn($customer);
            } elseif (!$session->isLoggedIn()) {
                $session->setCustomerAsLoggedIn($customer);
            }
        }

        return false;
    }

    public function restoreCartByQueueHash($hash)
    {
        $queue = $this->getQueue($hash);

        if ($queue) {
            $orderId       = $queue->getArg('order_id');
            $quoteId       = $queue->getArg('quote_id');
            $customerEmail = $queue->getArg('customer_email');
            $customerId    = $queue->getArg('customer_id');

            if ($quoteId) {
                /** @var QuoteModel $quote */
                $quote = $this->quoteFactory->create()->setSharedStoreIds(array_keys($this->storeManager->getStores()))
                    ->load($quoteId);

                if ($quote->getReservedOrderId()) {
                    return $this->restoreCartItems($quote->getAllVisibleItems());
                }

                $quote->setIsActive(true)->save();

                $this->session->replaceQuote($quote);

                return true;
            } elseif ($orderId) {
                /** @var Order $order */
                $order = $this->orderFactory->create()->load($orderId, false);

                return $this->restoreCartItems($order->getAllVisibleItems());
            } elseif (!$customerId && $customerEmail) { // guest
                $quote = $this->quoteHelper->getCartByCapturedEmail($customerEmail);

                if ($quote->getId()) {
                    if ($quote->getReservedOrderId()) {
                        return $this->restoreCartItems($quote->getAllVisibleItems());
                    }

                    $this->cart->truncate();

                    $quote->setIsActive(true)->save();

                    $this->session->replaceQuote($quote);
                    $this->cart->setQuote($this->session->getQuote())
                        ->save();

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Restore cart with given items.
     *
     * @param \Magento\Quote\Model\Quote\Item[]|\Magento\Sales\Model\Order\Item[] $items
     *
     * @return bool
     */
    private function restoreCartItems($items)
    {
        $cart = $this->cart;
        $cart->truncate();

        foreach ($items as $item) {
            try {
                if ($item instanceof \Magento\Quote\Model\Quote\Item) {
                    $cart->addProduct($item->getProduct(), $item->getBuyRequest());
                } else {
                    $cart->addOrderItem($item);
                }
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, 'Cannot add the item to shopping cart.');
            }
        }

        $cart->saveQuote();

        $this->session->replaceQuote($cart->getQuote());

        return true;
    }
}
