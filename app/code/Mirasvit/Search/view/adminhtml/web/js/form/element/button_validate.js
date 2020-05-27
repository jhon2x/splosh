define([
    'Magento_Ui/js/form/components/button',
    'uiRegistry',
    'jquery'
], function (Button, registry, $) {
    'use strict';

    return Button.extend({
        defaults: {},

        initialize: function () {
            this._super();
        },

        runTest: function () {
            // var config = registry.get(this.provider).data[this.type];
            var self = this;
            var sources = this.sourceNames;
            var sourceObject;
            var bindItem = this.bindTo;
            var data = {};
            
            $.each(sources, function(key,source){
                sourceObject = registry.get(source);
                data[sourceObject.parameter] = sourceObject.value(); 
            });

            $.ajax({
                showLoader: true,
                url: this.testUrl,
                data: data,
                type: 'GET',
                dataType: 'json'
            }).done(function (response) {
                $('[data-role=result-message]').remove();
                var message = $('<div>')
                    .css('margin-top', '10px')
                    .addClass('message')
                    .addClass('message-'+response.status)
                    .attr('data-role',  'result-message')
                    .html(response.result);

                message.appendTo('[data-index="'+bindItem+'"]');
            }).fail(function (response) {
                $('[data-role=result-message]').remove();
                var message = $('<div>')
                    .css('margin-top', '10px')
                    .addClass('message')
                    .addClass('message-error')
                    .attr('data-role', 'result-message')
                    .html(response.responseText);

                message.appendTo('[data-index="'+bindItem+'"]');
            });
        }
    });
});