/*global define*/
define([
    'jquery',
    'Magento_Ui/js/form/form',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry'
], function(
    $,
    Component,
    checkoutData,
    uiRegistry
) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            
            return this;
        },

        switchToForm: function(){
            $("#address-label").hide();
            $(".action-manual-address").hide();
            $(".clearer").hide();
            $("[name='addressAutocompleteForm.autocomplete_field']").hide();
            $("#address-autocomplete-form").find(".form-legend").hide();
            
            $("#shipping-new-address-form").find(".street").show();
            $("[name='shippingAddress.country_id']").show();
            $("[name='shippingAddress.postcode']").show();
            $("[name='shippingAddress.city']").show();
            $("[name='shippingAddress.region']").show();
        },

        clearField: function(){
            $("[name='autocomplete_field']").val('');
        },

        preventSubmit: function(){
            return false;
        }
    });
});