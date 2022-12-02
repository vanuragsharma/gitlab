# Geissweb_Euvat for Magento 2
All notable changes to this project will be documented in this file.

### [1.12.5] 2021-02-23
##### Added
- Show notice message when trying to validate a GB VAT number

### [1.12.4] 2021-02-19
##### Changed
- Removed billing address VAT number validation from Mageplaza Checkout due to unresolvable issues
##### Fixed
- Issue with logger on ThresholdCalculator class

### [1.12.3] 2021-01-28
##### Added
- Disable Cross-Border-Trade for non-EU when UK threshold is exceeded too
##### Fixed
- VAT number form field validation on Mageplaza Checkout

### [1.12.2] 2021-01-15
##### Added
- UK Threshold calculation now supports orders from the admin area

### [1.12.1] 2021-01-13
##### Added
- Validation for XI VAT numbers from Northern Ireland
- Treating Northern Ireland like an EU member state in regards of VAT calculation

##### Changed
- Adjusted Auto Setup for UK and Northern Ireland

##### Fixed
- EU VAT number validation when using a GB VAT number as requester number

### [1.12.0] 2020-12-21
##### Added
- Support UK sales threshold due to Brexit

### [1.11.0] 2020-12-03
##### Changed
- Removed support for spanish national numbers

### [1.10.1] 2020-12-01
##### Fixed
- Issue with form validation (when company field is filled)

### [1.10.0] 2020-10-23
##### Fixed
- Disable Cross-Border-Trade for valid VAT numbers when using offline validation fallback

##### Changed
- Improved integration with B2B registration (BSS extension), no template override needed anymore
- VAT field positioning on registration page

### [1.9.5] 2020-10-07
##### Fixed
- Issue with "no dynamic tax group" when the group should include tax in combination with "disabled cross-border-trade"

### [1.9.4] 2020-09-30
##### Fixed
- Issue with group select can not select "none" value

### [1.9.3] 2020-09-29
##### Added
- Allow to disable dynamic customer tax class calculation for the "not logged in" customer group

### [1.9.2] 2020-09-25
##### Changed
- Improvements to the console command "geissweb:clean:vatnumbers"
- Improved VAT number automatic formatting

### [1.9.1] 2020-09-24
##### Added
- Add country code to the VAT number if missing on revalidation of VAT numbers at customer login

### [1.9.0] 2020-09-18
##### Added
- Support for Spanish national tax numbers (NIF/CIF/NIE) in the VAT number field

##### Changed
- Improved validation messages for the admin area VAT number field at customer address edit
- Sales order create VAT field will now add the country prefix if missing

### [1.8.10] 2020-09-15
##### Changed
- Minor refactoring for vat-number-base.js: Return the AJAX promise in validateVatNumber(), extracted new method startValidation()

### [1.8.9] 2020-09-15
##### Fixed
- Issue with wrong tax calculation when using offline validation with the domestic country

### [1.8.8.1] 2020-09-14
##### Fixed
- Issue with Mageplaza checkout

### [1.8.8] 2020-09-09
##### Added
- New form field validation option: Required (accept any value)

##### Changed
- Uninstall script now removes vat_trader_name and vat_trader_address attributes and columns too

##### Fixed
- formElement configuration parameter is required

### [1.8.7.4] 2020-08-28
###### Changed
- System configuration country multiselect fields allow to select [-- None --]

### [1.8.7.3] 2020-08-28
###### Added
- Reloading of shipping methods after VAT number validation for Amasty Checkout

### [1.8.7.2] 2020-08-28
###### Fixed
- getCompany function

### [1.8.7.1] 2020-08-27
###### Fixed
- updateCountry and _ruleValidVat functions

### [1.8.7] 2020-08-26
###### Fixed
- Visibility issue with VAT field on customer address edit page

### [1.8.6] 2020-08-05
###### Changed
- More refactoring

### [1.8.5] 2020-08-03
###### Fixed
- Area code not set in console command

### [1.8.4] 2020-07-29
###### Added
- Console command to clear invalid VAT numbers from customer addresses
###### Fixed
- Issue with form field validation on Amasty checkout 

### [1.8.3.5] 2020-07-10
###### Changed
- Further strengthened the requirement of a valid VAT number at checkout for existing addresses

### [1.8.3.4] 2020-07-07
###### Added
- Handling for static customer group tax class while creating admin orders

### [1.8.3.3] 2020-07-01
###### Fixed
- Fixed typo in checkout validation

### [1.8.3.2] 2020-07-01
###### Changed
- When a valid VAT number is required for checkout, allow non-EU countries to complete the order without VAT number

### [1.8.3.1] 2020-06-26
###### Added
- Frontend translation for the new checkout validation message

### [1.8.3] 2020-06-26
###### Changed
- Additional validation for existing addresses, when a valid VAT number is required on checkout
- Refactor of OrderManagementInterface Plugin to remove usage of deprecated method

### [1.8.2] 2020-06-25
###### Fixed
- Issue with checkout field validation when the field was not visible

### [1.8.1] 2020-06-11
###### Fixed
- Bug when validating VAT numbers at the sales create order screen
- Reloading spinner loop at Mageplaza checkout when no shipping method is selected

### [1.8.0] 2020-06-09
###### Changed
- Dynamic shipping tax class now supports up to three different classes (default/reduced/super reduced or zero)

### [1.7.1] 2020-05-14
###### Changed
- Integration with Aheadworks Checkout, dropped support for versions below 1.7 for Aheadworks Checkout

### [1.7.0.2] 2020-04-09
###### Changed
- Refactor of system config for field visibility by country

### [1.7.0.1] 2020-04-06
###### Fixed
- Adminhtml Address Form compatibility 2.2/2.3.1

### [1.7.0] 2020-04-05
###### Changed
- Improved validation flow, better utilizing the offline fallback and reuse of existing validations
- Refactoring

### [1.6.0] 2020-02-24
###### Added
- Support for the new NL VAT number syntax

###### Changed
- Refactored field visibility, to allow to use the VAT field for non-EU numbers too

### [1.5.2] 2020-02-14
###### Fixed
- Wrong configuration scope for adminhtml orders

### [1.5.1] 2020-02-04
###### Added
- New field validation option to require a valid VAT number when the company field is filled

###### Changed
- New dedicated configuration options for the domestic country and the merchant VAT number

### [1.5.0.1] 2020-01-16
###### Added
- Improved field positioning at registration and customer address edit

###### Fixed
- Double trader information fields in adminhtml

###### Removed
- Specialized JS integration for Amasty Checkout module (not needed anymore)

### [1.5.0] 2019-11-22
###### Added
- Offline validation fallback with syntax check, applicable to selected countries
- Price display by customer group for "Cart Prices" and "Cart Subtotal"

###### Changed
- Some refactoring

### [1.4.1] 2019-10-21
###### Added
- Option to create tax rates (domestic rate) for non-EU countries while using the automatic setup

###### Fixed
- Country prefix autocomplete for Greece
- Fixed missing dependency for Magento\Tax\Model\Calculation

### [1.4.0.1] 2019-09-27
###### Fixed
- Compatibility with Magento 2.1.x and JSON decode

### [1.4.0] 2019-09-12
###### Added
- Option to always calculate VAT for selected countries, even with a valid VAT number
- Net VAT calculation works with threshold countries

### [1.3.11] 2019-08-16
###### Changed
- Admin validation on create order now uses store specific configuration
- Frontend AJAX validation now supports store code in URL properly

### [1.3.10] 2019-07-29
###### Added
- Checkout field visibility now respects address type of parentscope

### [1.3.9] 2019-07-18
###### Fixed
- Possible checkout error when company or vatid is missing on the address
- Possible group change confirm window loop

### [1.3.8] 2019-07-10
###### Changed
- Total reloading with Mageplaza Checkout
- Removed FIeldsetCompatibility class
- Read own version from composer.json

###### Fixed
- Issue with PayPal Captcha

### [1.3.7] 2019-07-02
###### Added
- VAT number validation data is saved to quote and order addresses

### [1.3.6.1] 2019-06-10
###### Changed
- Code cleanup

### [1.3.6] 2019-06-10
###### Changed
- Improved admin VAT validation on customer address edit page
- Totals reloading process at checkout

### [1.3.5.5] 2019-05-20
###### Changed
- Fixed issue with JS baseUrl

### [1.3.5.4] 2019-05-15
###### Changed
- Magento coding standard adjustments

###### Fixed
- Infinite loop when using dynamic shipping tax class

### [1.3.5.3] 2019-05-06
###### Changed
- Removed dependency on \Magento\Framework\App\Helper\AbstractHelper

### [1.3.5.2] 2019-04-30
###### Changed
- Use requester country from VAT number if present

### [1.3.5.1] 2019-04-08
###### Changed
- Compatibility with MagePlaza Checkout
- Compatibility with Magerun2, fixes customer:change-password command (Thanks to Alexander Menk)
- Magento_Checkout/js/model/cart/totals-processor/default::estimateTotals is executed after VAT number validation

### [1.3.5] 2019-03-31
###### Changed
- Adminhtml Customer Adress Edit VAT number validation (compatible with Magento 2.3.1)

### [1.3.4] 2019-02-07
###### Added
- Consideration of existing VAT number on cart estimate block
- Company name and VAT number rendering at all checkout addresses

###### Changed
- Compatibility with Amasty_Checkout 2.2.0

### [1.3.3] 2019-01-19
###### Fixed
- Fix for the Infinite Loop Fix

### [1.3.2] 2019-01-17
###### Added
- German Adminhtml Translation

###### Fixed
- Infinite Loop on Collect Totals

### [1.3.1] 2018-11-07
###### Added
- Solution for different tax rates on website level

###### Fixed
- Dynamic Shipping Tax

### [1.3.0] 2018-10-26
###### Added
- Feature to set catalog price display type per customer group

###### Fixed
- Performance improvement on the store configuration page

### [1.2.3] 2018-10-19
###### Fixed
- Issue on customer account save

### [1.2.2] 2018-10-04
###### Fixed
- VAT field visibility issue on Aheadworks Checkout

### [1.2.1] 2018-10-01
###### Fixed
- Issue at adminhtml_sales_order_create for M2.1
- Catalog price display excl. VAT

### [1.2.0] 2018-09-04
###### Added
- adminhtml_sales_order_create VAT number validation

###### Fixed
- Bug when changing store currency
- ACL issue

### [1.1.0] 2018-08-24
###### Fixed
- Issue with group assignment when customer creates the initial default address from customer account
- Issue with some themes not considering the JS path mappings correctly (use direct paths in JS components)

### [1.0.34] 2018-07-02
###### Fixed
- Bug with validation on Internet Explorer

###### Changed
- Ported checkout_submit_all_after event observer functions to a plugin

### [1.0.33] 2018-06-27
###### Changed
- Compatibility with Mageplaza Checkout

###### Added
- Support for admin orders with Cross-Border-Trade and Threshold countries
- Support for config checkout/options/display_billing_address_on (VAT validation on billing address at checkout)

### [1.0.32] 2018-06-19
###### Changed
- Compatibility with Amasty Checkout

### [1.0.31] 2018-06-06
###### Changed
- Field validation starts after configurable delay

### [1.0.30] 2018-05-29
###### Fixed
- Maintenance and minor Bugfixes

### [1.0.29] 2018-05-15
###### Added
- Possibility to revalidate VAT numbers on customer login within selectable periods

### [1.0.28] 2018-05-09
###### Added
- Possibility to disable dynamic tax class for specified customer groups

### [1.0.27] 2018-04-25
###### Added
- French translation

###### Changed
- Romania VAT rate to 19%
- Compatibility with Mageplaza Checkout

###### Fixed
- Empty response at validation

### [1.0.26] 2018-01-19
###### Fixed
- Adminhtml VAT fields

###### Added
- VAT number in checkout address renderer

### [1.0.25] 2018-01-12
###### Fixed
- AutoSetup product tax class mapping

###### Changed
- Country code is optional, if address country is present

### [1.0.24] 2017-12-21
###### Added 
- Compatibility with Aheadworks OneStepCheckout

###### Changed
- Mageplaza OSC compatibility

### [1.0.23] 2017-12-14
###### Added 
- New validation possibilities at Registration, Checkout and Customer Account (Address)

### [1.0.22] - 2017-11-22
###### Added
- Configurable debug logging
- Optional assignment of customer group on guest orders

###### Changed
- Reloading process for Magestore Checkout

### [1.0.21] - 2017-09-19
###### Changed
- Refactoring
- Threshold countries can now be all allowed countries

###### Fixed
- Minor Bugfixes


### [1.0.19] - 2017-08-03
###### Added
- Compatibility with Mageplaza Checkout

### [1.0.19] - 2017-07-25
###### Fixed
- Required VAT number at registration and address edit

### [1.0.18] - 2017-06-30
###### Changed
- Improved customer group assignment function

### [1.0.17] - 2017-06-28
###### Added
- Compatibility with OneStepCheckout (Amasty, Magestore)

### [1.0.16] - 2017-05-22
###### Added
- New shipping VAT algorithm allows to calculate the reduced VAT rate, if cart items have reduced VAT
- Support for Treshold countries when Cross-Border-Trade is used

### [1.0.15] - 2017-05-09
###### Fixed
- Several Bugfixes

###### Added
- Option to disable Cross-Border-Trade for EU customers with valid VAT number and for worldwide customers outside EU

### [1.0.0] - 2017-05-01
###### Added
- Initial Release
