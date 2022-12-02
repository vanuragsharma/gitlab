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
    'Geissweb_Euvat/js/form/element/vat-number-co',
    'Geissweb_Euvat/js/queue',
    'Geissweb_Euvat/js/model/reload',
    'mageUtils',
    'Magento_Ui/js/lib/validation/validator',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/set-billing-address',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/quote',
    'uiRegistry'
], function ($,
     VatNumberCo,
     queue,
     Reloader,
     Utils,
     validator,
     checkoutData,
     setBillingAddress,
     setShippingInfo,
     Quote,
     registry
) {
    'use strict';

    return VatNumberCo.extend({

        defaults: {
            imports: {
            }
        },

        initialize: function (options) {
            this._super();
            this.initObservable().setCssClasses();

            // Fix form validation for Mageplaza
            var self = this;
            //Add custom validation rules
            validator.addRule('valid-vat-required', function (value) {
                return self._ruleValidVat(value);
            }, this.validVatRequiredMessage);
            validator.addRule('valid-vat-if-specified', function (value) {
                return self._ruleValidVatIfSpecified(value);
            }, this.validVatIfSpecifiedMessage);
            validator.addRule('valid-vat-if-company-specified', function (value) {
                return self._ruleValidVatIfCompanySpecified(value);
            }, this.validVatIfCompanySpecifiedMessage);

            for (var property in this.validation) {
                if (this.validation.hasOwnProperty(property)
                    && property === 'valid-vat-required'
                ) {
                    _.extend(this.additionalClasses, {
                        _required: true
                    });
                }
            }

            if (this.debug) {
                console.log('vatNumberMP init '+self.uid, self);
            }
            return self;
        },

        setCssClasses: function () {
            if (this.containerClasses === null) {
                var containerClasses = ['field'];
                containerClasses.push('col-mp');
                containerClasses.push('mp-6');
                containerClasses.push('gw-euvat-field');
                containerClasses.forEach(function (name) {
                    this.containerClasses += " "+name;
                }, this);
                this.containerClasses = this.containerClasses.trim();
            }

            if (this.classes === null) {
                var classes = ['input-text', this.parentScope];
                for (var property in this.validation) {
                    if (this.validation.hasOwnProperty(property)) {
                        classes.push(property);
                    }
                }
                this.classes = "";
                classes.forEach(function (name) {
                    this.classes += " "+name;
                }, this);
                this.classes = this.classes.trim();
            }
        },

        afterValidation: function (jqXHR) {
            var self = this;

            queue.addFunction(function () {
                if (self.debug) {
                    console.log("co-reloader::addresses");
                }
                Reloader.addresses();
            });

            if (this.parentScope === 'billingAddress') {
                queue.addFunction(function () {
                    $.when(setBillingAddress()).done(function () {
                        if (self.debug) {
                            console.log("setBillingAddress done");
                        }
                    });
                });
            }

            if (this.parentScope === 'shippingAddress') {
                queue.addFunction(function () {
                    if (!Quote.isVirtual() && !Utils.isEmpty(Quote.shippingMethod())) {
                        $.when(setShippingInfo()).done(function () {
                            if (self.debug) {
                                console.log("setShippingInfo done");
                            }
                        });
                    }
                });
            }

            return queue.run();
        },

        /**
         * Form validation method
         * Require a valid VAT number validated through the validation service
         * @returns {*|boolean}
         * @private
         */
        _ruleValidVat: function (value) {
            var errMsg = this.error();
            var country = this.getCountry();
            if (typeof errMsg === 'undefined' || errMsg === false) {
                errMsg = '';
            }
            if (this.debug) {
                console.log('mageplaza _ruleValidVat', [
                    'this.value: '+this.value(),
                    'value: '+value,
                    'parentScope: '+this.parentScope,
                    'name: '+this.name,
                    'country: '+country,
                    'isValidated: ' +this.isValidated(),
                    'isValidVatNumber: '+this.isValidVatNumber(),
                    'this.passedRegex: '+this.passedRegex()
                ]);
            }

            // If the field is not visible, do not require validation
            if(!this.visible()) {
                if (this.debug) {
                    console.log("mageplaza _ruleValidVat not visible");
                }
                return true;
            }

            //Accept non-EU numbers as valid (valid in means of form validation), as they can't be validated at VIES
            if($.isArray(this.euCountries) && $.inArray(country, this.euCountries) === -1
                && this.getVatNumberCountry() === country
            ) {
                if (this.debug) {
                    console.log("mageplaza _ruleValidVat non-EU");
                }
                return true;
            }

            var actualValue;
            if(Utils.isEmpty(this.value())) {
                actualValue = value;
            } else {
                actualValue = this.value();
            }


            // We require a valid VAT number, if its empty it can not be valid
            if(Utils.isEmpty(actualValue)) {
                if (this.debug) {
                    console.log("mageplaza _ruleValidVat is empty");
                }
                return false;
            }

            //Actual validation
            if (this.visible()) {
                if (!this.enableAjaxValidation && this.passedRegex()) {
                    if (this.debug) {
                        console.log("mageplaza _ruleValidVat offline passed");
                    }
                    return true;
                }
                if (this.debug) {
                    console.log("isValid", [
                        this.isValidated(),
                        this.isValidVatNumber(),
                        (errMsg.length <= 0)
                    ]);
                }
                return this.isValidated() && this.isValidVatNumber() && (errMsg.length <= 0);

            } else {
                return true;
            }

        },

        /**
         * Form validation method
         * @returns {*|boolean}
         * @private
         */
        // Fix form validation for Mageplaza
        _ruleValidVatIfSpecified: function (value) {
            if (this.debug) {
                console.log(this);
                console.log('mageplaza _ruleValidVatIfSpecified', [
                    value,
                    this.visible(),
                    typeof(value),
                    value.length > 0
                ]);
            }
            if (this.visible() && typeof(value) === 'string' && value.length > 0) {
                console.log('mageplaza calling _ruleValidVat');
                return this._ruleValidVat(value);
            }
            return true;
        },

        /**
         * Form validation method
         * @returns {*|boolean}
         * @private
         */
        _ruleValidVatIfCompanySpecified: function (value) {
            if(!this.visible()) {
                return true;
            }
            var company = this.getCompany();
            if(!Utils.isEmpty(company)) {
                if(this.debug) {
                    console.log('Company not empty: '+company);
                }
                return this._ruleValidVat(value);
            } else {
                if(this.debug) {
                    console.log('Company empty: '+company);
                }
            }
            return true;
        },

        /**
         * Get the company field value in UiComponent forms
         * @returns {string|*}
         */
        // Fix form validation for Mageplaza
        getCompany: function () {
            if (this.debug) {
                console.log("mageplaza getCompany()");
            }
            var company;
            if(this.isBillingAddress) {
                company = registry.get('checkout.steps.shipping-step.billingAddress.billing-address-fieldset.company');
                if(typeof(company) !== "object") {
                    company = registry.get('checkout.steps.billing-step.payment.afterMethods.billing-address-form.form-fields.company');
                }
            } else {
                company = registry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.company');
            }

            if (typeof(company) !== 'object' || Utils.isEmpty(company.value())) {
                console.log("mageplaza getCompany() not able to find company field.", [
                    company.value(),
                    typeof(company),
                    Utils.isEmpty(company.value()),
                    company
                ]);
                return '';
            }
            if (this.debug) {
                console.log("mageplaza getCompany() result: "+company.value());
            }
            return company.value();
        },
    });
});
