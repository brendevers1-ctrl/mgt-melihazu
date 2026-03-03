define([
    'uiRegistry'
], function (registry) {
    'use strict';

    return {
        defaults: {
            mappings: {
                'product': {
                    'product_short_description': 'ns = product_form, index = short_description',
                    'product_description': 'ns = product_form, index = description',
                    'product_seo_name': 'ns = product_form, index = product_seo_name',
                    'product_meta_title': 'ns = product_form, index = meta_title',
                    'product_meta_keyword': 'ns = product_form, index = meta_keyword',
                    'product_meta_description': 'ns = product_form, index = meta_description',
                    'product_improve_short_description': 'ns = product_form, index = short_description',
                    'product_improve_description': 'ns = product_form, index = description',
                    'product_improve_seo_name': 'ns = product_form, index = product_seo_name',
                    'product_improve_meta_title': 'ns = product_form, index = meta_title',
                    'product_improve_meta_keyword': 'ns = product_form, index = meta_keyword',
                    'product_improve_meta_description': 'ns = product_form, index = meta_description',
                },
                'category': {
                    'category_description': 'ns = category_form, index = description',
                    'category_seo_name': 'ns = category_form, index = category_seo_name',
                    'category_meta_title': 'ns = category_form, index = meta_title',
                    'category_meta_keywords': 'ns = category_form, index = meta_keywords',
                    'category_meta_description': 'ns = category_form, index = meta_description',
                    'category_improve_description': 'ns = category_form, index = description',
                    'category_improve_seo_name': 'ns = category_form, index = category_seo_name',
                    'category_improve_meta_title': 'ns = category_form, index = meta_title',
                    'category_improve_meta_keywords': 'ns = category_form, index = meta_keywords',
                    'category_improve_meta_description': 'ns = category_form, index = meta_description'
                }
            }
        },

        getTargetComponent: function (messageType, entityType) {
            let entityMap = this.defaults.mappings[entityType],
                messageTypeToUse = typeof messageType === 'function' ? messageType() : messageType,
                entityTypeToUse = typeof entityType === 'function' ? entityType() : entityType;

            if (!entityMap) {
                throw new Error("Unknown entityType: " + entityTypeToUse);
            }

            let targetPath = entityMap[messageTypeToUse];
            if (!targetPath) {
                throw new Error("Unknown messageType for " + entityTypeToUse + ": " + messageTypeToUse);
            }

            let target = registry.get(targetPath);
            if (!target) {
                throw new Error("Component not found in form for messageType: " + messageTypeToUse);
            }

            return target;
        }
    };
});
