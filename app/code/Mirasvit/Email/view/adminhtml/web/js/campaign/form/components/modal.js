define([
    'Magento_Ui/js/modal/modal-component',
    'uiRegistry'
], function(Modal, registry) {
    'use strict';

    return Modal.extend({
        /**
         * Method retrieves title value from a source component and changes current modal's title.
         *
         * @param {string} sourceName - name of a source component holding modal's title
         * @param {string} methodName - setTitle or setSubTitle
         */
        fetchTitle: function(sourceName, methodName = 'setTitle') {
            var source = registry.get(sourceName);

            if (source.value()) {
                this[methodName](source.value());
            }
        }
    });
});