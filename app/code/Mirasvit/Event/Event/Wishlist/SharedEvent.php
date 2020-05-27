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

use Magento\Framework\Model\AbstractModel;
use Magento\Customer\Model\Customer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\CustomerData;
use Mirasvit\Event\EventData\WishlistData;
use Mirasvit\Event\EventData\StoreData;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResource;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;

class SharedEvent extends ObservableEvent
{
    const IDENTIFIER = 'wishlist_shared';

    const PARAM_PRODUCT_ID = 'product_id';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var WishlistFacotry
     */
    private $wishlistFacotry;

    public function __construct(
        WishlistFactory $wishlistFacotry,
        CustomerRepositoryInterface $customerRepository,
        Context $context
    ) {
        $this->wishlistFacotry = $wishlistFacotry;
        $this->customerRepository = $customerRepository;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [self::IDENTIFIER => __('Wishlist / Wishlist was shared')];
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        $wishlist = $this->context->create(WishlistData::class)->load($params[WishlistData::ID]);
        $customer = $this->context->create(CustomerData::class)->load($params[CustomerData::ID]);
        $store = $this->context->create(StoreData::class)->load($params[self::PARAM_STORE_ID]);

        $params[WishlistData::IDENTIFIER] = $wishlist;
        $params[CustomerData::IDENTIFIER] = $customer;
        $params[StoreData::IDENTIFIER] = $store;

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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        return __('%1 has shared wishlist', $params[self::PARAM_CUSTOMER_NAME]);
    }

    /**
     * @param WishlistResource       $subject
     * @param callable               $proceed
     * @param AbstractModel|Wishlist $wishlist
     *
     * @return mixed
     */
    public function aroundSave(WishlistResource $subject, callable $proceed, AbstractModel $wishlist)
    {
        /** @var Wishlist $oldWishlist */
        $oldWishlist = $this->wishlistFacotry->create()->load($wishlist->getId());
        // call original method
        $result = $proceed($wishlist);

        // register event
        if ($oldWishlist->getShared() < $wishlist->getShared()) {
            $this->register($wishlist);
        }

        return $result;
    }

    /**
     * Register event.
     *
     * @param Wishlist $wishlist
     */
    private function register(Wishlist $wishlist)
    {
        $customer = $this->customerRepository->getById($wishlist->getData('customer_id'));

        $params = [
            self::PARAM_CUSTOMER_EMAIL => $customer->getEmail(),
            self::PARAM_CUSTOMER_NAME  => $customer->getFirstname() . ' ' . $customer->getLastname(),
            CustomerData::ID           => $customer->getId(),
            self::PARAM_STORE_ID       => $customer->getStoreId(),
            WishlistData::ID           => $wishlist->getId(),
            self::PARAM_CREATED_AT     => $wishlist->getUpdatedAt(),
            self::PARAM_EXPIRE_AFTER   => 1
        ];

        $this->context->eventRepository->register(
            self::IDENTIFIER,
            [$params[WishlistData::ID]],
            $params
        );

        $this->context->timeService->setFlagTimestamp(self::IDENTIFIER);
    }
}
