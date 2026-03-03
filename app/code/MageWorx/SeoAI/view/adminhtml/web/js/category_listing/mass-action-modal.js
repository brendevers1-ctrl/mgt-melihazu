define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    return function (config) {
        const modalElement = $('#mageworx_seoai_massaction_modal'),
            popupModal = modal(config.options, modalElement);

        $('#mageworx_seoai_massaction').on('click', function (e) {
            e.preventDefault();
            popupModal.modal('openModal');
        });
    };
});
