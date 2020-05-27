define([
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract'
], function (uiRegistry, AbstractElement) {
    'use strict';

    return AbstractElement.extend({
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
        }
    });
});