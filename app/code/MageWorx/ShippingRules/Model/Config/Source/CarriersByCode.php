<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MageWorx\ShippingRules\Model\ResourceModel\Carrier\CollectionFactory as CarrierCollectionFactory;

/**
 * Class CarriersByCode
 */
class CarriersByCode implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @var CarrierCollectionFactory
     */
    private $carrierCollectionFactory;

    /**
     * CarriersByCode constructor.
     *
     * @param CarrierCollectionFactory $carrierCollectionFactory
     */
    public function __construct(
        CarrierCollectionFactory $carrierCollectionFactory
    ) {
        $this->carrierCollectionFactory = $carrierCollectionFactory;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        if (!empty($this->options)) {
            return $this->options;
        }

        /** @var \MageWorx\ShippingRules\Model\ResourceModel\Carrier\Collection $carrierCollection */
        $carrierCollection = $this->carrierCollectionFactory->create();
        $this->options = $carrierCollection->toOptionArray('carrier_code');

        return $this->options;
    }
}