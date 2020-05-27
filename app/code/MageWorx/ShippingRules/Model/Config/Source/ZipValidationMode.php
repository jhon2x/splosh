<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Config\Source;

use MageWorx\ShippingRules\Api\Data\RateInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ZipValidationMode
 */
class ZipValidationMode implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => RateInterface::ZIP_VALIDATION_MODE_NONE, 'label' => __('None')],
            ['value' => RateInterface::ZIP_VALIDATION_MODE_PLAIN, 'label' => __('Zip Codes List')],
            ['value' => RateInterface::ZIP_VALIDATION_MODE_DIAPASON, 'label' => __('Zip Codes Ranges')],
        ];
    }
}
