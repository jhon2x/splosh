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



namespace Mirasvit\Event\Event\Newsletter;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\CustomerData;
use Magento\Newsletter\Model\Subscriber;
use Mirasvit\Event\EventData\SubscriberData;

class SubscriptionEvent extends ObservableEvent
{
    const IDENTIFIER = 'subscription|';
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Context $context
    ) {
        parent::__construct($context);

        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            self::IDENTIFIER . 'subscribed'   => __('Newsletter / Customer was subscribed'),
            self::IDENTIFIER . 'unsubscribed' => __('Newsletter / Customer was unsubscribed'),
            self::IDENTIFIER . 'subscription_status_changed' => __('Newsletter / Customer subscription status change'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(CustomerData::class),
            $this->context->get(SubscriberData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        return __('Subscription status was changed');
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        $subscriberId = $params[SubscriberData::ID];
        $params[CustomerData::IDENTIFIER] = $this->context->get(CustomerData::class)->load($params[CustomerData::ID]);
        $params[SubscriberData::IDENTIFIER] = $this->context->get(SubscriberData::class)->load($subscriberId);

        return $params;
    }

    /**
     * @param Subscriber $subscriber
     * @return void
     */
    public function beforeSave(Subscriber $subscriber)
    {
        if ($subscriber->isStatusChanged()) {
            $params = [
                self::PARAM_STORE_ID       => $subscriber->getStoreId(),
                SubscriberData::ID         => $subscriber->getId(),
                CustomerData::ID           => $subscriber->getCustomerId(),
                self::PARAM_CUSTOMER_EMAIL => $subscriber->getEmail(),
                self::PARAM_CUSTOMER_NAME  => $this->getCustomerName($subscriber),
            ];

            $this->register(self::IDENTIFIER . 'subscription_status_changed', $params);

            if ($subscriber->getStatus() == Subscriber::STATUS_SUBSCRIBED) {
                $this->register(self::IDENTIFIER . 'subscribed', $params);
            } elseif ($subscriber->getStatus() == Subscriber::STATUS_UNSUBSCRIBED) {
                $this->register(self::IDENTIFIER . 'unsubscribed', $params);
            }
        }
    }

    /**
     * @param string $identifier
     * @param array $params
     */
    private function register($identifier, $params)
    {
        $this->context->eventRepository->register(
            $identifier,
            [$params[self::PARAM_CUSTOMER_EMAIL]],
            $params
        );
    }

    /**
     * Get customer name associated with subscriber.
     *
     * @param Subscriber $subscriber
     *
     * @return string
     */
    private function getCustomerName(Subscriber $subscriber)
    {
        $customer = null;
        $customerName = '';

        if ($subscriber->getCustomerId()) {
            try {
                $customer = $this->customerRepository->getById($subscriber->getId());
            } catch (\Exception $e) {} // mute exceptions

            if ($customer) {
                $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
            }
        }

        return $customerName;
    }
}
