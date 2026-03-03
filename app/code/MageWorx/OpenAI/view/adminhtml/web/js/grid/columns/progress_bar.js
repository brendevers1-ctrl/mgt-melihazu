/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/columns/column'
], function (_, Column) {
    'use strict';

    return Column.extend({
        getColor: function getColor(percent) {
            const value = Math.max(0, Math.min(100, parseFloat(percent))),
                startColor = {r: 201, g: 201, b: 201},
                endColor = {r: 30, g: 55, b: 90},
                r = Math.round(startColor.r + (endColor.r - startColor.r) * (value / 100)),
                g = Math.round(startColor.g + (endColor.g - startColor.g) * (value / 100)),
                b = Math.round(startColor.b + (endColor.b - startColor.b) * (value / 100));

            return 'rgb(' + r + ',' + g + ',' + b + ')';
        }
    });
});

