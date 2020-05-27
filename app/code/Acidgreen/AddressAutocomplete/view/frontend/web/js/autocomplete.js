define([
    'jquery',
    'uiComponent',
    'Acidgreen_AddressAutocomplete/js/google_maps_loader',
    'Magento_Checkout/js/checkout-data' ,
    'uiRegistry'
], function (
    $,
    Component,
    GoogleMapsLoader,
    checkoutData,
    uiRegistry
) {

    var componentForm = {
		subpremise: 'short_name',
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'long_name',
        country: 'short_name',
        postal_code: 'short_name'
    };

    var lookupElement = {
        street_number: 'street_1',
        route: 'street_2',
        locality: 'city',
        administrative_area_level_1: 'region',
        country: 'country_id',
        postal_code: 'postcode'
    };

    var autocomplete = '';

    GoogleMapsLoader.done(function () {
        var enabled = window.checkoutConfig.address_autocomplete.active;

        var geocoder = new google.maps.Geocoder();
        setTimeout(function () {
            if (enabled == '1') {
                var domID = '';
                var countryDomID = '';
                var countryCode = '';

                if(uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.address-autocomplete-form-container.address-autocomplete-form-fieldset.autocomplete_field'))
                    domID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.address-autocomplete-form-container.address-autocomplete-form-fieldset.autocomplete_field').uid;
                else
                    domID = "address_finder";

                if(uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.country_id')){
                    countryDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.country_id').uid;

                    if($('#'+countryDomID)){
                        countryCode = $('#'+countryDomID).val();
                    }
                } else{
                    countryDomID = 'country';
                    countryCode = $('#country').val();
                }

                var street = $('#'+domID);
                street.each(function () {
                    var element = this;
                    autocomplete = new google.maps.places.Autocomplete(
                        /** @type {!HTMLInputElement} */(this),
                        {
                            types: ['geocode'],
                            componentRestrictions: {country: countryCode}
                        }
                        );
                    autocomplete.addListener('place_changed', fillInAddress);
                });

                $('#'+domID).focus(geolocate);

                //filter search by country
                $('#'+countryDomID).on("change", function(){
                    autocomplete.setComponentRestrictions({'country': $(this).val()});
                });

                hideForm();
            }
        }, 5000);

    }).fail(function () {
        console.error("ERROR: Google maps library failed to load");
    });

    var fillInAddress = function () {
        var place = autocomplete.getPlace();

        var street = [];
        var region  = '';
		street[0] = '';
		street[1] = '';

        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (componentForm[addressType]) {
                var value = place.address_components[i][componentForm[addressType]];
				if (addressType == 'subpremise') {
                    street[0] += value + '/';
				} else if (addressType == 'street_number') {
				    var origPlace = autocomplete.gm_accessors_.place;
                    var str = "";
                    const keys = Object.keys(origPlace);
                    for (const key of keys) {
                        if(origPlace[key].formattedPrediction || "" ){
                            str = origPlace[key].formattedPrediction;
                            break;
                        }
                    }
                    var n = str.indexOf('/');
                    if (n!=-1 && !street[0].length){
                        street[0] += str.substring(0,n) + '/';
                    }
                    street[0] += value;
                } else if (addressType == 'route') {
                    street[1] = value;
                } else if (addressType == 'administrative_area_level_1') {
                    region = value;
                } else {
                    var elementId = lookupElement[addressType];
                    var thisDomID = '';

                    if(uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.'+ elementId))
                        thisDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.'+ elementId).uid;
                    else{
                        //for the my account postcode field
                        if(elementId == 'postcode')
                            elementId = 'zip'; 

                        if(elementId == 'country_id')
                            elementId = 'country';

                        thisDomID = elementId;
                    }

                    if ($('#'+thisDomID)) {
                        $('#'+thisDomID).val(value);
                        $('#'+thisDomID).trigger('change');

                        if($('#'+thisDomID).hasClass("select") || thisDomID == 'country')
                            $("#label-"+elementId).html($('#'+thisDomID).find("option:selected").text());
                        else
                            $("#label-"+elementId).html($('#'+thisDomID).val());
                    }
                }
            }
        }

        if (street.length > 0) {
            var domID = '';

            if(uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.street'))
                domID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.street').elems()[0].uid;
            else
                domID = lookupElement['street_number'];
            var streetString = street.join(' ');
            if ($('#'+domID)) {
                $('#'+domID).val(streetString);
                $('#'+domID).trigger('change');
            }

            $("#label-street").html(streetString);
        }

        if (region != '') {
            var regionDomId = '';

            if (uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id')) {
                regionDomId = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id').uid;
            } else if(uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id_input')){
                regionDomId = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id_input').uid;
            } else{
                regionDomId = "region_id";
            }

            if ($('#'+regionDomId)) {
                if(regionDomId == "region_id" || $('#'+regionDomId).hasClass("select")){
                    //search for and select region using text
                    $('#'+regionDomId +' option').filter(function(){
                        return $.trim($(this).text()) == region;
                    }).attr('selected',true);
                } else{
                    $('#'+regionDomId).val(region);

                    if($('#region'))
                        $('#region').val(region); //region text input on my account
                }

                $('#'+regionDomId).trigger('change');

                $("input[name='region']").val(region).trigger('keyup'); //force add value to region
                $("#label-region").html(region);
            }
        }

        switchToLabel();
    }

    var hideForm = function(){
        $("#shipping-new-address-form").find(".street").hide();
        $("[name='shippingAddress.country_id']").hide();
        $("[name='shippingAddress.postcode']").hide();
        $("[name='shippingAddress.city']").hide();
        $("[name='shippingAddress.region']").hide();
        
        $(".street,.country,.city,.region,.zip").hide();
    }

    var switchToLabel = function(){
        $("[name='addressAutocompleteForm.autocomplete_field']").hide();
        $("#address-autocomplete-form").find(".form-legend").show();
        $("#address-label").show();
        $(".action-manual-address").hide();
        $(".clearer").hide();

        $(".box-address-autocomplete").removeClass("hidden");
        $(".address-finder").addClass("hidden");
    }

    geolocate = function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                var geolocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                var circle = new google.maps.Circle({
                    center: geolocation,
                    radius: position.coords.accuracy
                });
                autocomplete.setBounds(circle.getBounds());
            });
        }
    }
    return Component;

});
