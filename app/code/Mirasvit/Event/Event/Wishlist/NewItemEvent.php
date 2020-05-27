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



namespace Mirasvit\Event\Event\Wishlist;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Customer\Model\Customer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\CustomerData;
use Mirasvit\Event\EventData\ProductData;
use Mirasvit\Event\EventData\WishlistData;
use Mirasvit\Event\EventData\StoreData;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\ResourceModel\Item as ItemResource;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;

class NewItemEvent extends ObservableEvent
{
    const IDENTIFIER = 'wishlist_item_new';

    const PARAM_PRODUCT_ID = 'product_id';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    public function __construct(
        WishlistFactory $wishlistFactory,
        CustomerRepositoryInterface $customerRepository,
        Context $context
    ) {
        $this->wishlistFactory = $wishlistFactory;
        $this->customerRepository = $customerRepository;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [self::IDENTIFIER => __('Wishlist / New product added to wishlist')];
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        $wishlist = $this->context->create(WishlistData::class)->load($params[WishlistData::ID]);
        $customer = $this->context->create(CustomerData::class)->load($params[CustomerData::ID]);
        $store = $this->context->create(StoreData::class)->load($params[self::PARAM_STORE_ID]);
        $product = $this->context->create(ProductInterface::class)->load($params[self::PARAM_PRODUCT_ID]);

        $params[WishlistData::IDENTIFIER] = $wishlist;
        $params[CustomerData::IDENTIFIER] = $customer;
        $params[StoreData::IDENTIFIER] = $store;
        $params[ProductData::IDENTIFIER] = $product;

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(WishlistData::class),
            $this->context->get(CustomerData::class),
            $this->context->get(StoreData::class),
            $this->context->get(ProductData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        $params = $this->expand($params);
        /** @var ProductInterface $product */
        $product = $params[ProductData::IDENTIFIER];

        return __('%1 has added product(sku: %2) to wishlist', $params[self::PARAM_CUSTOMER_NAME], $product->getSku());
    }

    /**
     * @param ItemResource               $subject
     * @param callable                   $proceed
     * @param AbstractModel|WishlistItem $wishlistItem
     *
     * @return mixed
     */
    public function aroundSave(ItemResource $subject, callable $proceed, AbstractModel $wishlistItem)
    {
        $isNew = $wishlistItem->isObjectNew();

        // call original method
        $result = $proceed($wishlistItem);

        if ($isNew) {
            $this->register($wishlistItem);
        }


        return $result;
    }

    /**
     * Register event.
     *
     * @param WishlistItem $wishlistItem
     */
    private function register(WishlistItem $wishlistItem)
    {
        /** @var Wishlist $wishlist */
        $wishlist = $this->wishlistFactory->create()->load($wishlistItem->getWishlistId());
        $customer = $this->customerRepository->getById($wishlist->getData('customer_id'));

        $params = [
            self::PARAM_CUSTOMER_EMAIL => $customer->getEmail(),
            self::PARAM_CUSTOMER_NAME  => $customer->getFirstname() . ' ' . $customer->getLastname(),
            CustomerData::ID           => $customer->getId(),
            self::PARAM_STORE_ID       => $wishlistItem->getStoreId(),
            ProductData::ID            => $wishlistItem->getProductId(),
            WishlistData::ID           => $wishlistItem->getWishlistId(),
            self::PARAM_CREATED_AT     => $wishlistItem->getAddedAt(),
            self::PARAM_EXPIRE_AFTER   => 30,
        ];

        $this->context->eventRepository->register(
            self::IDENTIFIER,
            [$params[WishlistData::ID]],
            $params
        );

        $this->context->timeService->setFlagTimestamp(self::IDENTIFIER);
    }
}
