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



namespace Mirasvit\Event\EventData\Order;


use Magento\Framework\Model\AbstractModel;
use Mirasvit\Event\Api\Data\AttributeInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\OrderCondition;
use Mirasvit\Event\EventData\OrderData;
use Mirasvit\Event\Api\Service\OptionsConverterInterface;
use Magento\Payment\Model\Config\Source\Allmethods;

class PaymentMethodAttribute implements AttributeInterface
{
    const ATTR_CODE  = 'payment_method';
    const ATTR_LABEL = 'Payment method';

    public function __construct(
        OptionsConverterInterface $optionsConverter,
        Allmethods $allPaymentMethods
    ) {
        $this->optionsConverter = $optionsConverter;
        $this->allPaymentMethods  = $allPaymentMethods;
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
        $payment = $this->optionsConverter->convert($this->allPaymentMethods->toOptionArray());

        return $payment;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return EventDataInterface::ATTRIBUTE_TYPE_ENUM;
    }

    public function getValue(AbstractModel $dataObject)
    {
        /** @var OrderData $order */
        $order = $dataObject->getData(OrderData::IDENTIFIER);

        $payment = $order->getPayment();

        return $payment ? $payment->getMethod() : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionClass()
    {
        return OrderCondition::class . '|' . self::ATTR_CODE;
    }
}
