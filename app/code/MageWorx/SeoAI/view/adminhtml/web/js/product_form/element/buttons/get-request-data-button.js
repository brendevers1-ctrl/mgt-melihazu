define([
    'jquery',
    'MageWorx_SeoAI/js/components/action-button-base',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'uiRegistry'
], function ($, BaseButton, alert, $t, registry) {
    'use strict';

    return BaseButton.extend({
        defaults: {
            entityType: 'product'
        },

        sendRequestAction: function () {
            let formData = this.getFormData();
            if (formData === null) {
                alert({
                    content: $t('Unable to process your request due to an error collecting form data. ' +
                        'Please refresh the page and try again. If the issue persists, ' +
                        'kindly contact Mageworx support for assistance.')
                });

                return;
            }

            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: formData,
                showLoader: true,
                success: function (response) {
                    let productFormSource = registry.get('product_form.product_form_data_source');

                    productFormSource.set(
                        'data.product.mageworx_seo.pregenerated_request_data.content',
                        response.content
                    );
                    if (response.context) {
                        productFormSource.set(
                            'data.product.mageworx_seo.pregenerated_request_data.context',
                            response.context.join("\n")
                        );
                    }
                },
                error: function (xhr, status, errorThrown) {
                    alert({
                        content: $t('Unable to process your request due to unknown error. ' +
                            'Please refresh the page and try again. If the issue persists, ' +
                            'kindly contact Mageworx support for assistance.')
                    });
                }
            });
        },

        getFormData: function () {
            let productFormSource = registry.get('product_form.product_form_data_source'),
                formData = productFormSource
                ? productFormSource.data.product.mageworx_seo
                : {},
                formKey = $(this.formKeySelector).val();

            if (!formKey) {
                return null;
            }

            formData.form_key = formKey;

            return formData;
        }
    });
});
