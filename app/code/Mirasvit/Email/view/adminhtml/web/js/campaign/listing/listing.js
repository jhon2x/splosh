define([
    'rjsResolver',
    'Magento_Ui/js/grid/listing',
    'uiRegistry'
], function (resolver, Listing, registry) {
    'use strict';

    return Listing.extend({
        initialize: function() {
            this._super();

            if (!this.hasData()) {
                // Load data when there will
                // be no more pending assets.
                resolver(this.source.reload, this.source);
            }

            return this;
        },

        createCampaign: function() {
            var modalNs = `${this.ns}.${this.ns}.modals.campaign_new_form_modal`,
                formNs  = `${this.ns}.${this.ns}.modals.campaign_new_form_modal.email_campaign_new_form`;

            registry.get(modalNs).toggleModal();
            registry.get(formNs).render();
        }
    });
});