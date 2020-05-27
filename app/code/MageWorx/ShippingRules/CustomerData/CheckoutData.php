<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use MageWorx\ShippingRules\Api\AddressResolverInterface;

/**
 * Class CheckoutData
 */
class CheckoutData implements SectionSourceInterface
{
    /**
     * @var AddressResolverInterface
     */
    protected $addressResolver;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \MageWorx\ShippingRules\Helper\Data
     */
    private $helper;

    /**
     * Location constructor.
     *
     * @param AddressResolverInterface $addressResolver
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \MageWorx\ShippingRules\Helper\Data $helper
     */
    public function __construct(
        AddressResolverInterface $addressResolver,
        \Magento\Customer\Model\Session $customerSession,
        \MageWorx\ShippingRules\Helper\Data $helper
    ) {
        $this->addressResolver = $addressResolver;
        $this->customerSession = $customerSession;
        $this->helper          = $helper;
    }

    /**
     * Get data for the checkout-data section:
     * country_id, region, region_id of the shipping & billing addresses
     * Used during checkout to fill address fields with default values based on the customers selection (popup) or
     * on the geoIp location.
     */
    public function getSectionData()
    {
        // Do nothing with customers data when popup disabled
        if (!$this->helper->isEnabledPopup()) {
            return [];
        }

        // Do not change data if customer logged in
        if ($this->customerSession->getCustomerId()) {
            if ($this->customerSession->getCustomer() &&
                $this->customerSession->getCustomer()->getDefaultShippingAddress() &&
                $this->customerSession->getCustomer()->getDefaultShippingAddress()->getCountryId()
            ) {
                return [];
            }
        }

        return [
            'shippingAddressFromData' => [
                'country_id' => $this->addressResolver->getCountryId(),
                'region'     => $this->addressResolver->getRegion(),
                'region_id'  => $this->addressResolver->getRegionId(),
            ],
            'billingAddressFormData'  => [
                'country_id' => $this->addressResolver->getCountryId(),
                'region'     => $this->addressResolver->getRegion(),
                'region_id'  => $this->addressResolver->getRegionId(),
            ],
        ];
    }
}
