<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Attribute\Frontend;

/**
 * Class AvailableShippingMethods
 *
 * Frontend model of the available_shipping_methods product's attribute
 */
class AvailableShippingMethods extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
{
    /**
     * @param \Magento\Framework\DataObject $object
     * @return mixed|string
     */
    public function getValue(\Magento\Framework\DataObject $object)
    {
        $value = parent::getValue($object);

        return $value;
    }
}