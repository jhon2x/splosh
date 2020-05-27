define([
    'underscore',
    'Magento_Ui/js/form/components/insert-form',
    'uiRegistry'
], function(_, InsertForm, registry) {
    'use strict';

    return InsertForm.extend({
        onState: function(state) {
            if (!state) {
                this.destroyInserted();
            }
        },

        ///**
        // * Set form entity id for sending request to load correct entity in DataProvider.
        // */
        //setEntityId: function(entityId) {
        //    this.params[this.entityFieldName] = entityId;
        //},

        /**
         * Re-render form every method call.
         */
        render: function (params) {
            // Re-render form each time
            this.destroyInserted();

            this._super(params);
        }

        ///**
        // * Add parameter sent with render action.
        // *
        // * @param {string} sourceName - name of a source component
        // * @param {string} paramName  - name of a parameter
        // */
        //addParamFromSource: function(sourceName, paramName) {
        //    var params = {campaign: {}};
        //    params.campaign[paramName] = registry.get(sourceName).value();
        //
        //    this.params = _.extend(this.params, params)
        //}
    })
});
