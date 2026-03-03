define([
    'Magento_Ui/js/form/element/select',
    'jquery',
    'uiRegistry'
], function (Select, $, registry) {
    'use strict';

    return Select.extend({
        /**
         * Custom initialization logic
         */
        initialize: function () {
            this._super();
            return this;
        },

        /**
         * Sets the value to the select field based on the action called from the button
         * @param {string} value - The value to set
         */
        setValue: function (value) {
            this.value(value);
        }
    });
});
