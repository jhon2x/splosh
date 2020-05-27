define([
    'jquery',
    'Mirasvit_Email/js/jqueryMask',
    'Magento_Ui/js/form/element/abstract'
], function($, mask, AbstractElement) {
    'use strict';

    return AbstractElement.extend({
        initialize: function () {
            this._super();

            /*$('.' + this.className).mask('DDD day(s) DD hour(s) DD minute(s)', {
                translation: {
                    'D': {pattern: /[0-9]/, optional: true}
                },
                placeholder: '___ day(s) __ hour(s) __ minute(s)'
            });*/

            $('.day-delay').mask('DDDD', {
                translation: {
                    'D': {pattern: /[0-9]/, optional: true}
                },
                selectOnFocus: true
            });
        }
    });
});
