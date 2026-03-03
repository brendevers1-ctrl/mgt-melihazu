define([
    'jquery',
    'Magento_Ui/js/form/components/button',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'uiRegistry',
    'MageWorx_SeoAI/js/utils/target-component-resolver'
], function ($, Button, alert, $t, registry, targetResolver) {
    'use strict';

    return Button.extend({
        defaults: {
            ajaxUrl: '',
            formKeySelector: 'input[name="form_key"]'
        },

        observableFields: [],

        initialize: function () {
            this._super();
            return this;
        },

        initObservable: function () {
            this._super();
            this.observe(this.observableFields);
            return this;
        },

        action: function () {
            this._super();
        },

        getTargetComponent: function (messageTypeToUse) {
            return targetResolver.getTargetComponent(messageTypeToUse, this.entityType);
        },

        // This method can be overridden by child components
        sendRequestAction: function () {},

        // This method can be overridden by child components
        getFormData: function () {}
    });
});
