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



namespace Mirasvit\Event\Event\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\CustomerData;
use Mirasvit\Event\EventData\StoreData;

class LoginLogoutEvent extends ObservableEvent
{
    const IDENTIFIER_LOGIN = 'customer_login';
    const IDENTIFIER_LOGOUT = 'customer_logout';

    const PARAM_CUSTOMER_ID = 'customer_id';

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            self::IDENTIFIER_LOGIN  => __('Customer / Logged In'),
            self::IDENTIFIER_LOGOUT => __('Customer / Logged Out'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(CustomerData::class),
            $this->context->get(StoreData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        $customer = $this->context->create(CustomerData::class)->load($params[CustomerData::ID]);
        $store = $this->context->create(StoreData::class)->load($params[self::PARAM_STORE_ID]);

        $params[CustomerData::IDENTIFIER] = $customer;
        $params[StoreData::IDENTIFIER] = $store;

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        return __('Customer Logged In/Out');
    }

    /**
     * @param Session $session
     * @return void
     */
    public function beforeLogout(Session $session)
    {
        if ($session->isLoggedIn()) {
            $customer = $session->getCustomerData();

            $params = [
                self::PARAM_CUSTOMER_ID    => $customer->getId(),
                self::PARAM_CUSTOMER_EMAIL => $customer->getEmail(),
                self::PARAM_CUSTOMER_NAME  => $customer->getFirstname() . ' ' . $customer->getLastname(),
                self::PARAM_STORE_ID       => $customer->getStoreId(),
            ];

            $this->context->eventRepository->register(
                self::IDENTIFIER_LOGOUT,
                [$customer->getId()],
                $params
            );
        }
    }

    /**
     * @param object $session
     * @param CustomerInterface $customer
     * @return void
     */
    public function beforeSetCustomerDataAsLoggedIn($session, CustomerInterface $customer)
    {
        $params = [
            self::PARAM_CUSTOMER_ID    => $customer->getId(),
            self::PARAM_CUSTOMER_EMAIL => $customer->getEmail(),
            self::PARAM_CUSTOMER_NAME  => $customer->getFirstname() . ' ' . $customer->getLastname(),
            self::PARAM_STORE_ID       => $customer->getStoreId(),
            self::PARAM_EXPIRE_AFTER   => 60 * 60 * 24 // expires after 1 day
        ];

        $this->context->eventRepository->register(
            self::IDENTIFIER_LOGIN,
            [$customer->getId()],
            $params
        );
    }
}