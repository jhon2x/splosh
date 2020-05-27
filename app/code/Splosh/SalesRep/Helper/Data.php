<?php

namespace Splosh\SalesRep\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;

class Data extends AbstractHelper
{
    /**
     * @var string
     */
    protected $countryCode = 'AU';

    /**
     * @var RegionCollection
     */
    protected $regionCollection;

    /**
     * Data constructor.
     * @param Context $context
     * @param RegionCollection $regionCollection
     */
    public function __construct(Context $context, RegionCollection $regionCollection)
    {
        parent::__construct($context);
        $this->regionCollection = $regionCollection;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $stateName
     * @return bool
     */
    public function getStatesId($stateName = '')
    {
        $result = false;
        if ($stateName) {
            $stateName = explode(',', $stateName);
            $stateName = array_map('strtolower', $stateName);
            $stateName = array_map('trim', $stateName);
            $collection = $this->regionCollection->addCountryFilter($this->getCountryCode());
            $matchedStates = [];
            foreach ($collection as $item => $value) {
                $regionName = strtolower(trim($value->getDefaultName()));
                if (in_array($regionName, $stateName)) {
                    $matchedStates[] = $value->getId();
                }
            }

            $result = implode(',', $matchedStates);
        }

        return $result;
    }
}