/**
 * Copyright © MageWorx, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry',
    'underscore',
    'mage/translate'
], function (AbstractField, registry, _, $t) {
    'use strict';

    return AbstractField.extend({
        defaults: {
            tracks: {
                plain_zip_visible: true,
                plain_zip_inversion_visible: true,
                zip_diapason_visible: true,
                zip_format_visible: true
            },

            exports: {
                plain_zip_visible: 'mageworx_shippingrules_rate_form.mageworx_shippingrules_rate_form.conditions.plain_zip_codes_string:visible',
                plain_zip_inversion_visible: 'mageworx_shippingrules_rate_form.mageworx_shippingrules_rate_form.conditions.plain_zip_codes_inversion:visible',
                zip_diapason_visible: 'mageworx_shippingrules_rate_form.mageworx_shippingrules_rate_form.conditions.zip_code_diapasons:visible',
                zip_format_visible: 'mageworx_shippingrules_rate_form.mageworx_shippingrules_rate_form.conditions.zip_format:visible'
            }
        },

        initObservable: function () {
            this._super();
            this.observe('plain_zip_visible plain_zip_inversion_visible zip_diapason_visible zip_format_visible');

            this.value.subscribe(function (value) {
                console.log(value);
                if (value == 0) {
                    this.plain_zip_visible(false);
                    this.plain_zip_inversion_visible(false);
                    this.zip_diapason_visible(false);
                    this.zip_format_visible(false);
                } else if (value == 1) {
                    this.plain_zip_visible(true);
                    this.plain_zip_inversion_visible(true);
                    this.zip_diapason_visible(false);
                    this.zip_format_visible(false);
                } else if (value == 2) {
                    this.plain_zip_visible(false);
                    this.plain_zip_inversion_visible(false);
                    this.zip_diapason_visible(true);
                    this.zip_format_visible(true);
                }
            }, this);

            return this;
        }
    });
});
