/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_Ui/js/grid/columns/multiselect'
], function ($, _, $t, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Mirasvit_Email/grid/cells/onoff',
            fieldClass: {
                'admin__scope-old': true,
                'data-grid-onoff-cell': true,
                'data-grid-checkbox-cell': false
            }
        },

        /**
         * @param {Number} id
         * @returns {*}
         */
        getLabel: function (id) {
            return this.selected.indexOf(id) !== -1 ? $t('On') : $t('Off');
        },

        toggleState: function (component, event) {
            var entityId  = event.target.value,
                data      = { selected: [entityId], namespace: this.ns };

            data[this.indexField] = + event.target.checked; // + to convert boolean to int

            $.ajax(this.actionUrl, {
                data: data,
                type: 'POST',
                showLoader: true
                // context or loaderContext for loader context
            });
        }
    });
});
