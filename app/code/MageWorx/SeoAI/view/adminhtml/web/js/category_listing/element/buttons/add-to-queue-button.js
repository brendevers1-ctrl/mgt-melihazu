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

                    alert({content: 'Request is added to generation queue'});
                }.bind(this),
                error: function (xhr, status, errorThrown) {
                    let message;

                    switch (xhr.status) {
                        case 408:
                        case 504:
                            message = $t('Unable to process your request due to unknown error. ' +
                                    'Please refresh the page and try again. If the issue persists, ' +
                                    'kindly contact Mageworx support for assistance.') +
                                ' <br/><hr/>' +
                                $t('Please review the list of generation processes. ' +
                                    'There is a chance that the latest task doesn\'t contain all items ' +
                                    'you requested data generation for.');
                            break;
                        default:
                            message = $t('Unable to process your request due to unknown error. ' +
                                'Please refresh the page and try again. If the issue persists, ' +
                                'kindly contact Mageworx support for assistance.');
                    }

                    alert({
                        content: message
                    });
                }
            });
        },

        getFormData: function () {
            let formSource = registry.get('mageworx_seocategorygrid_categorygrid_listing.mageworx_seocategorygrid_categorygrid_listing_data_source'),
                formData,
                formKey = $(this.formKeySelector).val();

            if (formSource) {
                formData = Object.assign({}, formSource.mageworx_seo_ai);
            } else {
                formData = {};
            }

            let selections = this.getMassActionSelections();
            if (selections !== null) {
                _.extend(formData, selections);
            }

            if (!formKey) {
                return null;
            }

            formData.form_key = formKey;

            return formData;
        },

        getMassActionSelections: function () {
            let massAction = registry.get("mageworx_seocategorygrid_categorygrid_listing.mageworx_seocategorygrid_categorygrid_listing.listing_top.listing_massaction");
            if (massAction === null) {
                return null;
            }

            let data = massAction.getSelections();
            if (data === null) {
                return null;
            }

            let itemsType = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            return selections;
        }
    });
});
