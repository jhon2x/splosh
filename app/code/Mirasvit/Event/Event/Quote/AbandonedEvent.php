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



namespace Mirasvit\Event\Event\Quote;

use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Model\Quote;
use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\CronEvent;
use Mirasvit\Event\EventData\AddressShippingData;
use Mirasvit\Event\EventData\CustomerData;
use Mirasvit\Event\EventData\QuoteData;
use Mirasvit\Event\EventData\StoreData;

class AbandonedEvent extends CronEvent
{
    const IDENTIFIER = 'quote_abandoned';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Context $context
    ) {
        $this->resource = $resourceConnection;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [self::IDENTIFIER => __('Shopping Cart / Abandoned Shopping Cart')];
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        /** @var QuoteData $quote */
        $quote = $this->context->create(QuoteData::class)->loadByIdWithoutStore($params[QuoteData::ID]);
        /** @var CustomerData $customer */
        $customer = $this->context->create(CustomerData::class)->load($params[CustomerData::ID]);
        $store = $this->context->create(StoreData::class)->load($params[self::PARAM_STORE_ID]);

        $params[QuoteData::IDENTIFIER] = $quote;
        $params[CustomerData::IDENTIFIER] = $customer;
        $params[StoreData::IDENTIFIER] = $store;
        $params[AddressShippingData::IDENTIFIER] = $quote->getShippingAddress()->getId()
            ? $quote->getShippingAddress()
            : $customer->getPrimaryShippingAddress();

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(QuoteData::class),
            $this->context->get(CustomerData::class),
            $this->context->get(StoreData::class),
            $this->context->get(AddressShippingData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        return __('Shopping Cart was abandoned');
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $lastCheck = $this->context->timeService->getFlagTimestamp(self::IDENTIFIER);

        $connection = $this->resource->getConnection();

        /** select carts, that created between 1:30hour...1hour ago */
        $updatedAtFrom = $this->context->timeService->shiftDateTime(90 * 60, $lastCheck);
        $updatedAtTo = $this->context->timeService->shiftDateTime(60 * 60, $lastCheck);

        $select = $connection->select()
            ->from(
                ['q' => $this->resource->getTableName('quote')],
                [
                    'store_id'    => 'q.store_id',
                    'quote_id'    => 'q.entity_id',
                    'customer_id' => 'q.customer_id',
                    'updated_at'  => 'q.updated_at',
                    'created_at'  => 'q.created_at',
                ]
            )
            ->joinLeft(
                ['qa' => $this->resource->getTableName('quote_address')],
                'q.entity_id = qa.quote_id AND qa.address_type = "billing"',
                [
                    'customer_email'     => new \Zend_Db_Expr('IFNULL(q.customer_email, qa.email)'),
                    'customer_firstname' => new \Zend_Db_Expr('IFNULL(q.customer_firstname, qa.firstname)'),
                    'customer_lastname'  => new \Zend_Db_Expr('IFNULL(q.customer_lastname, qa.lastname)'),
                ]
            )
            ->joinInner(
                ['qi' => $this->resource->getTableName('quote_item')],
                'q.entity_id = qi.quote_id',
                [
                    'i_created_at' => new \Zend_Db_Expr('MAX(qi.created_at)'),
                ]
            )
            ->joinLeft(array('order' => $this->resource->getTableName('sales_order')),
                'order.quote_id = q.entity_id',
                array()
            )
            ->where('order.entity_id IS NULL')
            ->where('q.is_active = 1')
            ->where('q.items_count > 0')
            ->where('q.customer_email IS NOT NULL OR qa.email IS NOT NULL')
            ->where('qi.parent_item_id IS NULL')
            ->group('q.entity_id')
            ->having(
                '(q.created_at > ? OR MAX(qi.created_at) > ?)',
                $updatedAtFrom
            )
            ->having(
                '(q.created_at < ? OR MAX(qi.created_at) < ?)',
                $updatedAtTo
            )
            ->order('q.updated_at');

        $quotes = $connection->fetchAll($select);

        foreach ($quotes as $quote) {
            $params = [
                self::PARAM_EXPIRE_AFTER   => 7 * 24 * 60 * 60 + 1,
                self::PARAM_STORE_ID       => $quote['store_id'],
                QuoteData::ID              => $quote['quote_id'],
                CustomerData::ID           => $quote['customer_id'],
                self::PARAM_CUSTOMER_EMAIL => $quote['customer_email'],
                self::PARAM_CUSTOMER_NAME  => $quote['customer_firstname'] . ' ' . $quote['customer_lastname'],
                self::PARAM_CREATED_AT     => max($quote['created_at'], $quote['i_created_at']),
            ];

            $this->context->eventRepository->register(
                self::IDENTIFIER,
                [$params[QuoteData::ID]],
                $params
            );
        }

        $this->context->timeService->setFlagTimestamp(self::IDENTIFIER);
    }
}
