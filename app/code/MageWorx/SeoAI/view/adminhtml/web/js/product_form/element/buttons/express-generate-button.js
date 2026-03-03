"use strict";
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
                        console.log('Empty response or error.');
                        if (response.error && response.error.message) {
                            alert({
                                content: response.error.message
                            });
                        }
                    } else {
                        let targetComponent;

                        try {
                            targetComponent = this.getTargetComponent(messageType);
                        } catch (e) {
                            console.log(e);
                            return;
                        }

                        targetComponent.value(responseSelected);

                        let editorId = targetComponent.wysiwygId;
                        if (editorId) {
                            let editorInstance = tinyMCE.get(editorId);
                            if (editorInstance) {
                                editorInstance.setContent(responseSelected);
                            }
                        }

                        if (typeof targetComponent.setData === 'function') {
                            targetComponent.setData(responseSelected);
                        }
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
            let formSource = registry.get('product_form.product_form_data_source'),
                formKey = $(this.formKeySelector).val();

            if (!formKey) {
                return null;
            }

            return {
                product_id: formSource.get('data.product.current_product_id'),
                store_id: formSource.get('data.product.current_store_id'),
                message_type: messageType,
                form_key: formKey
            };
        }
    });
});
