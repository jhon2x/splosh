<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Acidgreen\AddressAutocomplete\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class AutocompleteConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Acidgreen\AddressAutocomplete\Helper\Data
     */
    private $helper;

    /**
     * @param \Acidgreen\AddressAutocomplete\Helper\Data $helper
     */
    public function __construct(
        \Acidgreen\AddressAutocomplete\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config['address_autocomplete'] = [
            'active'        => $this->helper->getConfig('shipping/address_autocomplete/active'),
            'api_key'  =>    $this->helper->getConfig('shipping/address_autocomplete/google_api_key')
        ];
        return $config;
    }
}
