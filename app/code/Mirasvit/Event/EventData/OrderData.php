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



namespace Mirasvit\Event\EventData;

use Magento\Sales\Model\Order;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\Api\Service\OptionsConverterInterface;
use Mirasvit\Event\EventData\Condition\OrderCondition;

class OrderData extends Order implements EventDataInterface
{
    use ContextTrait;

    const ID = 'order_id';
    const IDENTIFIER = 'order';

    public function getIdentifier()
    {
        return 'order';
    }

    public function getConditionClass()
    {
        return OrderCondition::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return __('Order');
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        /** @var OptionsConverterInterface $converter */
        $converter = $this->get(OptionsConverterInterface::class);

        $shipping = $converter->convert($this->get('Magento\Shipping\Model\Config\Source\Allmethods')->toOptionArray());
        $status = $this->get('Magento\Sales\Model\Order\Config')->getStatuses();

        $attributes = [
            'grand_total'       => [
                'label' => __('Grand Total'),
                'type'  => self::ATTRIBUTE_TYPE_NUMBER,
            ],
            'shipping_method'   => [
                'label'   => __('Shipping Method'),
                'type'    => self::ATTRIBUTE_TYPE_ENUM,
                'options' => $shipping,
            ],
            'is_order_shipped'  => [
                'label' => __('Shipment created'),
                'type'  => self::ATTRIBUTE_TYPE_BOOL,
            ],
            'status'      => [
                'label'   => __('Status'),
                'type'    => self::ATTRIBUTE_TYPE_ENUM,
                'options' => $status,
            ],
            'is_order_invoiced' => [
                'label' => __('Invoice created'),
                'type'  => self::ATTRIBUTE_TYPE_BOOL,
            ],
            'updated_at' => [
                'label' => __('Updated At'),
                'type'  => self::ATTRIBUTE_TYPE_DATE,
            ],
            'created_at' => [
                'label' => __('Created At'),
                'type'  => self::ATTRIBUTE_TYPE_DATE,
            ]
        ];

        return $attributes;
    }
}