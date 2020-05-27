
var google_maps_loaded_def = null;

define([
    'jquery'
],function ($) {

    if (!google_maps_loaded_def) {
        google_maps_loaded_def = $.Deferred();
        window.google_maps_loaded = function () {
            google_maps_loaded_def.resolve(google.maps);
        }

        var apiKey = '';

        if(typeof window.checkoutConfig !== "undefined")
            apiKey =  window.checkoutConfig.address_autocomplete.api_key;
        else
            apiKey = 'AIzaSyBsfJaI6bstiwPVNsevWJ8hseRZh_5WPSQ';

        if (apiKey != 'false') {
            var url = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&libraries=places&callback=google_maps_loaded';
            require([url], function () {}, function (err) {
                google_maps_loaded_def.reject();
            });
        }
    }
    return google_maps_loaded_def.promise();
});