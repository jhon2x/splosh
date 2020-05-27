define([
    'jquery',
    'Magento_Ui/js/form/element/textarea',
    'uiRegistry',
    './variables'

], function($, Textarea, registry, variables) {
    'use strict';

    return Textarea.extend({
        initialize: function() {
            this._super();

            return this;
        },

        /**
         * Open variables popup.
         */
        openVariables: function() {
            Variables.init(this.uid);
            Variables.openVariableChooser(this.getVariables());
        },

        /**
         * Retrieve variables from the parent component.
         *
         * @return {Array}
         */
        getVariables: function() {
            if (!this.variables) {
                var editor = registry.get(this.ns + '.' + this.ns + '.general.editor');
                this.variables = editor.variables;
            }

            return this.variables;
        }
    });
});