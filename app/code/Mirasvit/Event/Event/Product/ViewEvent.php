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



namespace Mirasvit\Event\Event\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\ResourceModel\Stock;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\CustomerData;
use Mirasvit\Event\EventData\ProductData;
use Mirasvit\Event\EventData\StoreData;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite as UrlRewriteConstants;

class ViewEvent extends ObservableEvent implements ObserverInterface
{
    const IDENTIFIER = 'product_view';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * ViewEvent constructor.
     *
     * @param UrlFinderInterface    $urlFinder
     * @param CheckoutSession       $checkoutSession
     * @param CustomerSession       $customerSession
     * @param StoreManagerInterface $storeManager
     * @param Context               $context
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        parent::__construct($context);

        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->urlFinder = $urlFinder;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            self::IDENTIFIER => __('Product / View'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(ProductData::class),
            $this->context->get(CustomerData::class),
            $this->context->get(StoreData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        $product = $this->context->create(Product::class)->load($params[ProductData::ID]);
        $customer = $this->context->create(CustomerData::class)->load($params[CustomerData::ID]);
        $store = $this->context->create(StoreData::class)->load($params[self::PARAM_STORE_ID]);

        $params[ProductData::IDENTIFIER] = $product;
        $params[CustomerData::IDENTIFIER] = $customer;
        $params[StoreData::IDENTIFIER] = $store;

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        $params = $this->expand($params);

        /** @var Product $product */
        $product = $params[ProductData::IDENTIFIER];

        return __('Product %1 %2 has been viewed by %3',
            $product->getSku(),
            $product->getProductUrl(),
            ($params[self::PARAM_CUSTOMER_NAME] ?: 'customer')
            . ' (' . ($params[self::PARAM_CUSTOMER_ID] ?: 'guest') . ')'

        );
    }

    /**
     * Register product view event.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $observer->getEvent()->getData('request');
        $storeId = $this->storeManager->getStore()->getId();
        $rewrite = $this->getRewrite($request->getPathInfo(), $storeId);

        if ($this->canRegister($request, $rewrite)) {
            $params = $this->prepareParams($request, $rewrite);
            $params[self::PARAM_STORE_ID] = $storeId;
            // 7 day limit for the same product viewed by customer in the same store
            $params[self::PARAM_EXPIRE_AFTER] = 60 * 60 * 24 * 7;

            if ($params[self::PARAM_CUSTOMER_EMAIL]) {
                $this->context->eventRepository->register(
                    self::IDENTIFIER,
                    [$params[ProductData::ID], $storeId, $params[self::PARAM_CUSTOMER_EMAIL]],
                    $params
                );
            }
        }
    }

    /**
     * @param string $requestPath
     * @param int $storeId
     * @return UrlRewrite|null
     */
    private function getRewrite($requestPath, $storeId)
    {
        return $this->urlFinder->findOneByData([
            UrlRewrite::REQUEST_PATH => ltrim($requestPath, '/'),
            UrlRewrite::STORE_ID => $storeId,
        ]);
    }

    /**
     * Check whether the product view event can be registered or not.
     *
     * First request to product is not cached so the $request object itself contains all the needed info.
     * When a product page is cached we should check $rewrite object instead.
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param UrlRewrite|null                     $rewrite
     *
     * @return bool
     */
    private function canRegister(\Magento\Framework\App\Request\Http $request, UrlRewrite $rewrite = null)
    {
        if (($request->getControllerName() === 'product' && $request->getParam('id'))
            || ($rewrite !== null
            && $rewrite->getEntityType() == UrlRewriteConstants::ENTITY_TYPE_PRODUCT // only products
            && !$rewrite->getRedirectType()) // without redirect
        ) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve event params.
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param UrlRewrite|null                     $rewrite
     *
     * @return array
     */
    private function prepareParams(\Magento\Framework\App\Request\Http $request, UrlRewrite $rewrite = null)
    {
        $productId = $rewrite !== null ? $rewrite->getEntityId() : $request->getParam('id');
        $customer = ($this->customerSession->getCustomerId()) ? $this->customerSession->getCustomer() : false;
        if ($customer) {
            $customerEmail = $customer->getEmail();
            $customerName = $customer->getName();
            $customerId = $customer->getId();
        } else {
            $customerId = null;
            $quote = $this->checkoutSession->getQuote();
            $customerEmail = ($quote->getCustomerEmail()) ?: $quote->getBillingAddress()->getEmail();
            // customer email can be saved to session by capture script
            $customerEmail = $customerEmail ?: $this->customerSession->getEmail();
            $firstname = ($quote->getCustomerFirstname()) ?: $quote->getBillingAddress()->getFirstname();
            $lastname = ($quote->getCustomerLastname()) ?: $quote->getBillingAddress()->getLastname();
            $customerName = $firstname . ' ' . $lastname;
        }

        return [
            ProductData::ID => $productId,
            self::PARAM_CUSTOMER_EMAIL => $customerEmail,
            self::PARAM_CUSTOMER_ID => $customerId,
            self::PARAM_CUSTOMER_NAME => trim($customerName)
        ];
    }
}
