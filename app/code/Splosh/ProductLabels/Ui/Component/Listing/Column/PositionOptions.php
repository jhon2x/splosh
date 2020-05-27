<?php

namespace Splosh\ProductLabels\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Splosh\ProductLabels\Helper\Data;

/**
 * Class PositionOptions
 * @package Splosh\ProductLabels\Ui\Component\Listing\Column
 */
class PositionOptions implements OptionSourceInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['label' => 'Top Right', 'value' => Data::POSITION_OPTION_TOP_RIGHT],
            ['label' => 'Top Center', 'value' => Data::POSITION_OPTION_TOP_CENTER],
            ['label' => 'Top Left', 'value' => Data::POSITION_OPTION_TOP_LEFT],
            ['label' => 'Middle Left', 'value' => Data::POSITION_OPTION_MIDDLE_LEFT],
            ['label' => 'Middle Right', 'value' => Data::POSITION_OPTION_MIDDLE_RIGHT],
            ['label' => 'Bottom Left', 'value' => Data::POSITION_OPTION_BOTTOM_LEFT],
            ['label' => 'Bottom Center', 'value' => Data::POSITION_OPTION_BOTTOM_CENTER],
            ['label' => 'Bottom Right', 'value' => Data::POSITION_OPTION_BOTTOM_RIGHT]
        ];

        return $options;
    }
}