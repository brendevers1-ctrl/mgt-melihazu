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

        sendRequestAction: function (messageType) {
            let formData = this.getFormData(messageType);
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
                    let responseSelected = response.choices?.[0]?.message?.content || null;

                    if (responseSelected === null) {
                        console.log('Empty response!');
                    } else {
                        navigator.clipboard.writeText(responseSelected).then(
                            () => {
                                alert({
                                    content: $t('Text successfully copied to clipboard.')
                                });
                            },
                            () => {
                                alert({
                                    content: $t('Failed to copy text to clipboard.')
                                });
                            },
                        );
                    }
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

        getFormData: function (messageType) {
            let formSource = registry.get('category_form.category_form_data_source'),
                formKey = $(this.formKeySelector).val();

            if (!formKey) {
                return null;
            }

            return {
                category_id: formSource.get('data.entity_id'),
                store_id: formSource.get('data.store_id'),
                message_type: messageType,
                form_key: formKey
            };
        }
    });
});
