/**
 * ||GEISSWEB| EU VAT Enhanced
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL: https://www.geissweb.de/legal-information/eula
 *
 * DISCLAIMER
 *
 * Do not edit this file if you wish to update the extension in the future. If you wish to customize the extension
 * for your needs please refer to our support for more information.
 *
 * @copyright   Copyright (c) 2015 GEISS Weblösungen (https://www.geissweb.de)
 * @license     https://www.geissweb.de/legal-information/eula GEISSWEB End User License Agreement
 */

define([
    'jquery',
    'mage/translate',
    'mage/url',
    'mageUtils'
],function (
    $,
    $t,
    url,
    Utils
) {
    'use strict';

    return {

        countryCode: '',

        setCountryCode: function(countryCode) {
            this.countryCode = countryCode;
        },

        hasCountryPrefix: function(vatNumber) {
            return !!vatNumber.match(new RegExp('^[A-Z][A-Z]'));
        },

        addCountryPrefix: function(vatNumber) {
            if(!Utils.isEmpty(this.countryCode)) {
                return this.countryCode+vatNumber;
            }
            return vatNumber;
        },

        isValidSyntax: function (vatNumber) {
            if(!this.hasCountryPrefix(vatNumber)) {
                vatNumber = this.addCountryPrefix(vatNumber);
            }
            return new RegExp(this.patterns[this.countryCode]).test(vatNumber);
        },

        patterns: {
            'AT' : '(AT)U[0-9]{8}$',
            'BE' : '(BE)0[0-9]{9}$',
            'BG' : '(BG)[0-9]{9,10}$',
            'CY' : '(CY)[0-9]{8}[A-Z]$',
            'CZ' : '(CZ)[0-9]{8,10}$',
            'DE' : '(DE)[0-9]{9}$',
            'DK' : '(DK)[0-9]{8}$',
            'EE' : '(EE)[0-9]{9}$',
            'GR' : '(EL|GR)[0-9]{9}$',
            'EL' : '(EL|GR)[0-9]{9}$',
            'ES' : '(ES)[0-9A-Z][0-9]{7}[0-9A-Z]$',
            'FI' : '(FI)[0-9]{8}$',
            'FR' : '(FR)[0-9A-Z]{2}[0-9]{9}$',
            'GB' : '(GB)([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3}$)',
            'HR' : '(HR)[0-9]{11}$',
            'HU' : '(HU)[0-9]{8}$',
            'IE' : '(IE)(([0-9]{7}WI|[0-9][0-9A-Z\*\+][0-9]{5}[A-Z]{1,2}$))',
            'IT' : '(IT)[0-9]{11}$',
            'LT' : '(LT)([0-9]{9}|[0-9]{12}$)',
            'LU' : '(LU)[0-9]{8}$',
            'LV' : '(LV)[0-9]{11}$',
            'MT' : '(MT)[0-9]{8}$',
            'NL' : '(NL)[0-9]{9}B([0-9]{2}|O[0-9]{1}$)',
            'PL' : '(PL)[0-9]{10}$',
            'PT' : '(PT)[0-9]{9}$',
            'RO' : '(RO)[0-9]{2,10}$',
            'SE' : '(SE)[0-9]{12}$',
            'SI' : '(SI)[0-9]{8}$',
            'SK' : '(SK)[0-9]{10}$'
        }
    }
});