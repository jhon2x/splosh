<?php

namespace Splosh\ProductLabels\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Splosh\ProductLabels\Model\Label;

/**
 * Class IsActive
 * @package Splosh\ProductLabels\Model\Source
 */
class IsActive implements OptionSourceInterface
{
    /**
     * @var Label
     */
    protected $labelModel;

    /**
     * IsActive constructor.
     * @param Label $labelModel
     */
    public function __construct(Label $labelModel)
    {
        $this->labelModel = $labelModel;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $labelOptions = $this->labelModel->labelStatuses();
        $options = [];

        foreach ($labelOptions as $item => $value) {
            $options[] = ['label' => $value, 'value' => $item];
        }

        return $options;
    }
}