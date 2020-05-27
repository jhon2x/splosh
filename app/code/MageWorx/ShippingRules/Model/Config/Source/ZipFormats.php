<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MageWorx\ShippingRules\Model\ZipCodeManager;

/**
 * Class ZipFormats
 */
class ZipFormats implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => ZipCodeManager::NUMERIC_FORMAT,
                'label' => __('Numeric')
            ],
            [
                'value' => ZipCodeManager::ALPHANUMERIC_FORMAT_UK,
                'label' => __('Alphanumeric (UK)')
            ],
            [
                'value' => ZipCodeManager::ALPHANUMERIC_FORMAT_NL,
                'label' => __('Alphanumeric (NL)')
            ],
            [
                'value' => ZipCodeManager::ALPHANUMERIC_FORMAT,
                'label' => __('Alphanumeric (Other)')
            ],
        ];

        return $options;
    }
}
