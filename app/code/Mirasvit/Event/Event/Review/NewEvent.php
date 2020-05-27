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



namespace Mirasvit\Event\Event\Review;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\CronEvent;
use Mirasvit\Event\EventData\CustomerData;
use Mirasvit\Event\EventData\ProductData;
use Mirasvit\Event\EventData\ReviewData;
use Mirasvit\Event\EventData\StoreData;

class NewEvent extends CronEvent
{
    const IDENTIFIER = 'review_new';

    const PARAM_PRODUCT_ID = 'product_id';

    /**
     * @var ReviewCollectionFactory
     */
    private $reviewCollectionFactory;
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        CustomerFactory $customerFactory,
        ReviewCollectionFactory $reviewCollectionFactory,
        Context $context
    ) {
        $this->customerFactory = $customerFactory;
        $this->reviewCollectionFactory = $reviewCollectionFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [self::IDENTIFIER => __('Review / New review was added')];
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        $review = $this->context->create(ReviewData::class)->load($params[ReviewData::ID]);
        $customer = $this->context->create(CustomerData::class)->load($params[CustomerData::ID]);
        $store = $this->context->create(StoreData::class)->load($params[self::PARAM_STORE_ID]);
        $product = $this->context->create(ProductInterface::class)->load($params[self::PARAM_PRODUCT_ID]);

        $params[ReviewData::IDENTIFIER] = $review;
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
            $this->context->get(ReviewData::class),
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
        return __('New review was added');
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $lastCheck = $this->context->timeService->getFlagDateTime(self::IDENTIFIER);

        $collection = $this->reviewCollectionFactory->create()->addStoreData();
        $collection->getSelect()->where('created_at >= ?', $lastCheck);

        /** @var \Magento\Review\Model\Review $review */
        foreach ($collection as $review) {
            $stores = $review->getData('stores');
            sort($stores);
            if ($review->getData('customer_id')) {
                /** @var CustomerData $customer */
                $customer = $this->customerFactory->create()->load($review->getData('customer_id'));
                $customerEmail = $customer->getEmail();
                $customerName = $customer->getName();
            } else {
                $customerEmail = '';
                $customerName = $review->getData('nickname');
            }

            $params = [
                self::PARAM_STORE_ID       => end($stores),
                ReviewData::ID             => $review->getId(),
                CustomerData::ID           => $review->getData('customer_id'),
                self::PARAM_PRODUCT_ID     => $review->getEntityPkValue(),
                self::PARAM_CUSTOMER_EMAIL => $customerEmail,
                self::PARAM_CUSTOMER_NAME  => $customerName,
                self::PARAM_CREATED_AT     => $review->getData('created_at'),
            ];

            $this->context->eventRepository->register(
                self::IDENTIFIER,
                [$params[ReviewData::ID]],
                $params
            );
        }

        $this->context->timeService->setFlagTimestamp(self::IDENTIFIER);
    }
}
