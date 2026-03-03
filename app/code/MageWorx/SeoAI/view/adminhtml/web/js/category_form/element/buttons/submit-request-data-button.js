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
            entityType: 'category'
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
                    if (response.error) {
                        alert({
                            content: response.error.message ?? 'Request error'
                        });

                        return;
                    }

                    let resultsContainer = registry.get(
                            'category_form.category_form.seo_generate_modal.seo_generate_results.response_container'
                        ),
                        results = (resultsContainer.responses() ?? []);

                    results = results.concat(response.choices.map(
                        choice => choice.message ? choice.message.content : choice.text)
                    );
                    resultsContainer.responses(results);
                }.bind(this),
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
            let formSource = registry.get('category_form.category_form_data_source'),
                formData = formSource
                    ? formSource.data.mageworx_seo
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
