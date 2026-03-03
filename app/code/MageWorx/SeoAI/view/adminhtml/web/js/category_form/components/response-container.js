define([
    'MageWorx_SeoAI/js/components/response-container-base',
    'MageWorx_SeoAI/js/utils/target-component-resolver',
    'uiRegistry'
], function (BaseComponent, targetResolver, registry) {
    'use strict';

    return BaseComponent.extend({
        defaults: {
            imports: {
                messageType: 'category_form.category_form.seo_generate_modal.seo_generate_system.message_type:value'
            },
            entityType: 'category'
        },

        closeModal: function () {
            registry.get('category_form.category_form.seo_generate_modal').actionDone();
        }
    });
});
