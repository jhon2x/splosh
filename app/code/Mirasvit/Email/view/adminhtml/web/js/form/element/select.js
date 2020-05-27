define([
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (uiRegistry, Select) {
    'use strict';

    return Select.extend({
        initialize: function() {
            this._super();

            if (this.depends) {
                var fieldName = this.name.replace(this.index, this.depends);
                var field = uiRegistry.get(fieldName);
                if (field) {
                    if (this.visibleValue == field.value()) {
                        this.show();
                    } else {
                        this.hide();
                    }
                }
            }
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {

            if (this.depending) {
                for (var key in this.depending) {
                    if (this.depending.hasOwnProperty(key)) {
                        var fieldName = this.name.replace(this.index, this.depending[key]);
                        var field = uiRegistry.get(fieldName);
                        if (field) {
                            if (field.visibleValue == value) {
                                field.show();
                            } else {
                                field.hide();
                            }
                        }
                    }
                }
            }

            return this._super();
        }
    });
});