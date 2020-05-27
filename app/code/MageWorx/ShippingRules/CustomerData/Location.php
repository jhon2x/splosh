<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use MageWorx\ShippingRules\Api\AddressResolverInterface;

/**
 * Class Location
 *
 *
 * Used to store and retrieve customers location data on the frontend.
 *
 * @see MageWorx/ShippingRules/view/frontend/web/js/location.js
 */
class Location implements SectionSourceInterface
{
    /**
     * @var AddressResolverInterface
     */
    protected $addressResolver;

    /**
     * @var \MageWorx\ShippingRules\Helper\Data
     */
    private $helper;

    /**
     * Location constructor.
     *
     * @param AddressResolverInterface $addressResolver
     */
    public function __construct(
        AddressResolverInterface $addressResolver,
        \MageWorx\ShippingRules\Helper\Data $helper
    ) {
        $this->addressResolver = $addressResolver;
        $this->helper          = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        // Do nothing with customers data when popup disabled
        if (!$this->helper->isEnabledPopup()) {
            return [];
        }

        $data = [
            'country_code'   => $this->addressResolver->getCountryId(),
            'country'        => $this->addressResolver->getCountryName(),
            'region_code'    => $this->addressResolver->getRegionCode(),
            'region'         => $this->addressResolver->getRegion(),
            'regionJsonList' => $this->addressResolver->getRegionJsonList()
        ];

        return $data;
    }
}
