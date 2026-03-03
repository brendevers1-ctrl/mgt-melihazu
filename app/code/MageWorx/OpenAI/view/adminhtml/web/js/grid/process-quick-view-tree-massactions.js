/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/grid/tree-massactions'
], function (ko, _, utils, TreeMassActions) {
    'use strict';

    return TreeMassActions.extend({
        defaults: {
            process_id: null,
            imports: {
                process_id: 'mageworx_openai_process_listing.mageworx_openai_process_listing_data_source:params.process_id'
            }
        },

        /**
         * Callback function for mass action.
         * @see _getCallback function of parent class 'massaction.js'
         *
         * @param action
         * @param data
         */
        addProcessIdToCallback: function (action, data) {
            data['params'] = data['params'] || {};
            data['params']['process_id'] = this.process_id;

            this.defaultCallback(action, data);
        }
    });
});
