define([
    'jquery',
    'mage/utils/wrapper',
    'mage/template'
], function ($, wrapper, mageTemplate) {
    'use strict';

    return function (targetModule) {
        targetModule.formatPriceLocale = wrapper.wrapSuper(targetModule.formatPriceLocale, function (amount, format, isShowSign) {
            return this.formatPrice(amount, format, isShowSign);
        });

        return targetModule;
    };
});