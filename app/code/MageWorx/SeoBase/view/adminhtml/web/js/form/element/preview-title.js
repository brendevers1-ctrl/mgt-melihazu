/**
 * Copyright © Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
], function ($, Abstract) {
    'use strict';

    return Abstract.extend({
        /**
         * @param {String} value
         */
        prepareValue: function (value) {
            if (value) {
                var sliced = value.slice(0, 60);

                if (sliced.length < value.length) {
                    sliced += '...';
                }

                this.value(sliced);
            } else {
                this.value('');
            }
        },
    });
});