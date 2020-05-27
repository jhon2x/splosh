/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
var config = {
    map: {
        '*': {
            shippingZoneSelector: 'MageWorx_ShippingRules/js/zone/selector'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-rates-validation-rules': {
                'MageWorx_ShippingRules/js/checkout/model/shipping-rates-validation-rules-mixin': true
            },
            'Mageplaza_Osc/js/model/shipping-rates-validator': {
                'MageWorx_ShippingRules/js/checkout/model/shipping-rates-validation-rules-mixin': true
            }
        }
    }
};
