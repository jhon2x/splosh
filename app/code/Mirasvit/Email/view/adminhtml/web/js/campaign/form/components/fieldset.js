define([
    'Magento_Ui/js/form/components/fieldset'
], function (Fieldset) {
    return Fieldset.extend({
        initObservable: function () {
            this._super()
                .observe('label');

            return this;
        },

        /**
         * Toggle fieldset visibility.
         */
        toggleVisibility: function () {
            this.visible(!this.visible());
        }
    })
});
