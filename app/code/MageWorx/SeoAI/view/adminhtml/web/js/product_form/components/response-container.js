define([
    'MageWorx_SeoAI/js/components/response-container-base',
    'MageWorx_SeoAI/js/utils/target-component-resolver',
    'uiRegistry'
], function (BaseComponent, targetResolver, registry) {
    'use strict';

    return BaseComponent.extend({
        defaults: {
            imports: {
                messageType: 'product_form.product_form.seo_generate_modal.seo_generate_system.message_type:value'
            },
            entityType: 'product'
        },

        closeModal: function () {
            registry.get('product_form.product_form.seo_generate_modal').actionDone();
        }
    });
});
