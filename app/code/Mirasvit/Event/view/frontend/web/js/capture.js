define([
    'jquery',
    'underscore',
    'jquery/ui'
], function ($, _) {
    'use strict';
    $.widget('mirasvit.emailCapture', {
        options: {},

        _create: function () {
            var self = this;

            _.bindAll(this, 'save', 'capture');

            setInterval(function () {
                var inputs = $('[type=text], [type=email]');
                _.each(inputs, function (input) {
                    $(input).off('change', self.capture)
                        .on('change', self.capture);
                });
            }, 1000);
        },

        capture: function (e) {
            var $input = $(e.srcElement);
            var name = $input.attr('name');
            var value = $input.val();

            switch (name) {
                case 'firstname':
                    this.save('firstname', value);
                    break;

                case 'lastname':
                    this.save('lastname', value);
                    break;

                case 'email':
                case 'username':
                    var expr = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    if (expr.test(value)) {
                        this.save('email', value);
                    }
                    break;
            }
        },

        save: function (type, value) {
            $.ajax(this.options.url, {
                method: 'post',
                data: {
                    type: type,
                    value: value
                }
            });
        }
    });

    return $.mirasvit.emailCapture;
});