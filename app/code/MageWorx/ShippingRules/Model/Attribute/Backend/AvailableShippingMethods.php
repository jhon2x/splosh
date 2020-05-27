<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Attribute\Backend;

/**
 * Class AvailableShippingMethods
 *
 * Backend model of the available_shipping_methods product's attribute
 */
class AvailableShippingMethods extends \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend
{
    /**
     * Validate
     *
     * @param \Magento\Catalog\Model\Product $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function validate($object)
    {
        return parent::validate($object);
    }
}