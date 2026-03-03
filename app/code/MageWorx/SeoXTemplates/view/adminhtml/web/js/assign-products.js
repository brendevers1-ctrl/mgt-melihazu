/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

/* global $, $H */

define([
    'jquery',
    'mage/adminhtml/grid'
], function (jQuery) {
    'use strict';

    return function (config) {
        var selectedProducts = config.selectedProducts,
            templateProducts = $H(selectedProducts),
            gridJsObject = window[config.gridJsObjectName],
            tabIndex = 1000;

        $('in_template_products').value = Object.toJSON(templateProducts);

        /**
         * Register Template Product
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function registerTemplateProduct(grid, element, checked) {

            if (!isNaN(element.value)) {
                if (checked) {
                    templateProducts.set(element.value, element.value);
                } else {
                    templateProducts.unset(element.value);
                }

                $('in_template_products').value = Object.toJSON(templateProducts);

                updateSelectedCounter();

                grid.reloadParams = {
                    'selected_products[]': templateProducts.keys()
                };
            }
        }

        /**
         * Click on product row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function templateProductRowClick(grid, event) {

            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT',
                checked = false,
                checkbox = null;

            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');

                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }

        /**
         * Initialize template product row
         *
         * @param {Object} grid
         * @param {String} row
         */
        function templateProductRowInit(grid, row) {
            updateSelectedCounter();
        }

        /**
         * Update counter
         */
        function updateSelectedCounter() {
            var selectedIds = [];
            var selectedCounter = 0;
            templateProducts.each(function (item) {
                if (typeof item.value !== 'function') {
                    selectedIds.push(item.key);
                    selectedCounter += 1;
                }
            });

            jQuery('#product_grid_massaction-count')
                .find(("strong[data-role='counter']"))
                .html(selectedCounter);
        }

        gridJsObject.checkboxCheckCallback = registerTemplateProduct;
        gridJsObject.rowClickCallback = templateProductRowClick;
        gridJsObject.initRowCallback = templateProductRowInit;

        if (gridJsObject.rows) {
            gridJsObject.rows.each(function (row) {
                templateProductRowInit(gridJsObject, row);
            });
        }
    };
});