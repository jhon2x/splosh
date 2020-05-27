<?php

namespace Splosh\SalesRep\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;

class States implements OptionSourceInterface
{
    /**
     * @var RegionCollection
     */
    protected $regionCollection;

    /**
     * @var string
     */
    protected $countryCode = 'AU';

    /**
     * States constructor.
     * @param RegionCollection $regionCollection
     */
    public function __construct(RegionCollection $regionCollection)
    {
        $this->regionCollection = $regionCollection->addCountryFilter($this->countryCode);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = $this->regionCollection->toOptionArray();
        unset($options[0]);
        return $options;
    }
}