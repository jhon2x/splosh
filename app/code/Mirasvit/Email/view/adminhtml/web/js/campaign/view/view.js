define([
    'Magento_Ui/js/form/form',
    'uiRegistry',
    'jquery',
    'mage/translate'
], function (Form, registry, $, $t) {
    'use strict';
    
    return Form.extend({
        defaults: {
            chains: [],
            imports: {
                data: '${ $.provider }:data'
            }
        },
        
        $campaign: function() {
            return this.data['general'];
        },

        showModal: function(type, params) {
            var modalNs = `${this.ns}.${this.ns}.modals.${type}_edit_form_modal`,
                formNs  = `${this.ns}.${this.ns}.modals.${type}_edit_form_modal.email_${type}_edit_form`,
                modal   = registry.get(modalNs),
                form    = registry.get(formNs);

            modal.toggleModal();
            form.render(params);
        },

        scrollToTrigger: function(el, component) {
            if (location.hash) {
                var targetId = location.hash.replace('#', '');
                if (el.id === targetId) {
                    window.scroll(0, el.parentElement.offsetTop - 100);
                }
            }
        },

        getToggleLabel: function(isActive) {
            return Number(isActive) ? $t('Disable') : $t('Enable');
        }
    })
});
