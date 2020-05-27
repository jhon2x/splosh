define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/ui-select'
], function ($, registry, Select) {
    'use strict';

    return Select.extend({
        /**
         * On value change handler.
         */
        onUpdate: function () {
            this._super();

            //this.updateRuleComponent();
        },

        /**
         * Toggle activity list element
         *
         * @param {Object} data - selected option data
         * @returns {Object} Chainable
         */
        toggleOptionSelected: function(data) {
            if (data.hasOwnProperty('optgroup') || !data.path) {
                return this;
            }

            return this._super();
        },

        /**
         * Update rule component content according to selected event.
         */
        updateRuleComponent: function() {
            var rule = registry.get(this.ruleName);

            $.ajax(this.ruleUrl, {
                type: 'POST',
                data: {event: this.value},
                showLoader: true,
                success: function(response) {
                    rule.updateContent(response.html);
                }
            });
        }
    });
});
