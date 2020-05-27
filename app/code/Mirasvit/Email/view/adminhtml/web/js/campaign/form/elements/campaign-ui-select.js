define([
    'underscore',
    'Magento_Ui/js/form/element/ui-select',
    'uiRegistry',
    'mage/translate'
], function (_, UiSelect, registry, $t) {
    'use strict';

    return UiSelect.extend({
        defaults: {
            currentCampaign: $t('New Campaign'),

            exports: {
                currentCampaign: '${ $.ns }.${ $.ns }.step_two:label'
            }
        },

        initObservable: function () {
            this._super();

            this.observe('currentCampaign');

            return this;
        },

        toggleOptionSelected: function (data) {
            let stepOne = registry.get(this.parentName),
                stepTwo = registry.get(this.stepTwo);

            this._super(data);

            this.currentCampaign(data.label);

            stepOne.visible(false);
            // set campaign title
            registry.get(stepTwo.name + '.title').value(this.currentCampaign());
            stepTwo.visible(true);
            // set campaign_template_id
            registry.get(stepTwo.name + '.template_id').value(data.value);
        }
    });
});
