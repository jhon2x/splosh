define([
    'Magento_SalesRule/js/view/summary/discount',
    'jquery',
    'Magento_Checkout/js/model/quote'
], function (Component, $, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_Rules/summary/discount-breakdown',
            rules: false,
            cartSelector: '.cart-summary tr.totals',
            checkoutSelector: '.totals.discount'
        },

        initObservable: function () {
            this._super();
            this.observe(['rules']);

            return this;
        },

        /**
         * initialize
         */
        initialize: function () {
            this._super();
            this.initCollapseBreakdown();
            this.rules(this.getRules());
            quote.totals.subscribe(this.getDiscountDataFromTotals.bind(this));
        },

        /**
         * getRules
         */
        getRules: function () {
            return this.amount.length ? this.amount : '';
        },

        /**
         * @override
         *
         * @returns {Boolean}
         */
        isDisplayed: function () {
            return this.getPureValue() != 0;
        },

        /**
         * @override
         *
         * @returns {Boolean}
         */
        initCollapseBreakdown: function () {
            $(document).on('click', this.checkoutSelector, this.collapseBreakdown);
            $(document).on('click', this.cartSelector, this.collapseBreakdown);
        },

        collapseBreakdown: function () {
            $('.total-rules').toggle();
            $(this).find('.title').toggleClass('-collapsed');
        },

        showDiscountArrow: function () {
            $('.totals .title').addClass('-enabled');
        },

        /**
         * @param {Array} totals
         */
        getDiscountDataFromTotals: function (totals) {
            if (totals.extension_attributes && totals.extension_attributes.amrule_discount_breakdown) {
                this.rules(totals.extension_attributes.amrule_discount_breakdown);
            } else {
                this.rules(null);
            }
        }
    });
});
