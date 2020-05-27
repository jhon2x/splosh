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



namespace Mirasvit\Event\EventData\Customer;


use Magento\Framework\Model\AbstractModel;
use Mirasvit\Event\Api\Data\AttributeInterface;
use Mirasvit\Event\Api\Data\Event\InstanceEventInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\CustomerCondition;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Sales\Model\ResourceModel\Sale\CollectionFactory as SaleCollectionFactory;

class SubscriptionStatusAttribute extends CustomerAbstractAttribute implements AttributeInterface
{
    const ATTR_CODE  = 'subscription_status';
    const ATTR_LABEL = 'Newsletter subscription status';
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    public function __construct(
        SubscriberFactory $subscriberFactory,
        SaleCollectionFactory $saleCollectionFactory
    ) {
        $this->subscriberFactory = $subscriberFactory;

        parent::__construct($saleCollectionFactory);
    }

    /**
     * {@inheritDoc}
     */
    public function getCode()
    {
        return self::ATTR_CODE;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return __(self::ATTR_LABEL);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return [
            Subscriber::STATUS_SUBSCRIBED   => __('Subscribed'),
            Subscriber::STATUS_NOT_ACTIVE   => __('Not Activated'),
            Subscriber::STATUS_UNSUBSCRIBED => __('Unsubscribed'),
            Subscriber::STATUS_UNCONFIRMED  => __('Unconfirmed'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return EventDataInterface::ATTRIBUTE_TYPE_ENUM;
    }

    /**
     * Check whether customer is subscriber or not.
     *
     * {@inheritDoc}
     */
    public function getValue(AbstractModel $dataObject)
    {
        /** @var Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByEmail($dataObject->getData(InstanceEventInterface::PARAM_CUSTOMER_EMAIL));

        return $subscriber->getStatus();
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionClass()
    {
        return CustomerCondition::class . '|' . self::ATTR_CODE;
    }
}
