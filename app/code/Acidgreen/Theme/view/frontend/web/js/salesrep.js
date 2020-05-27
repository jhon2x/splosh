define([
    'jquery',
    'uiComponent',
    'ko'
], function($, Component, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            salesrep: {
                block: ''
            }
        },

        initialize: function() {
            this._super().initObservable();

            this.getSalesrep();
        },

        initObservable: function() {
            this._super().observe('salesrep');

            return this;
        },

        getSalesrep: function() {
            var salesrep = this.salesrep;

            return $.ajax({
                url : '/acidgreentheme/salesrep/index',
                type: 'GET',
                contentType: 'text/json',
                dataType: 'json',
                success: function(data) {
                    salesrep(data);
                }
            });
        }
    });
});
