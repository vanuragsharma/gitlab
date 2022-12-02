<?php
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

namespace Geissweb\Euvat\Plugin\CheckoutLayout;

use Geissweb\Euvat\Helper\Configuration;
use Geissweb\Euvat\Logger\Logger;
use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Framework\Module\Manager;

/**
 * Class CheckoutLayout
 */
class DefaultCheckout
{
    /**
     * @var Configuration
     */
    public $configHelper;

    /**
     * @var Manager
     */
    public $moduleManager;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * CheckoutLayout constructor.
     *
     * @param Configuration $config
     * @param Manager    $moduleManager
     * @param Logger     $logger
     */
    public function __construct(
        Configuration $config,
        Manager $moduleManager,
        Logger $logger
    ) {
        $this->configHelper = $config;
        $this->moduleManager = $moduleManager;
        $this->logger = $logger;
    }

    /**
     * @param LayoutProcessor $subject
     * @param array $jsLayout
     * @return array $jsLayout
     */
    public function afterProcess(
        LayoutProcessor $subject,
        array $jsLayout
    ) {
        $originalLayout = [];
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['vat_id'])
        ) {
            $originalLayout = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['vat_id'];
            $this->logger->debug("OriginalLayout: " . var_export($originalLayout, true));
        }

        $formFieldValidationAtCheckout = $this->configHelper->getFieldValidationAtCheckout();

        $configParam = isset($originalLayout['config']) ? $originalLayout['config'] : [];
        $fieldLayout = [
            'label' => __('VAT Number'),
            'component' => 'Geissweb_Euvat/js/form/element/vat-number-co',
            'config' => $this->configHelper->getVatFieldConfig($configParam, 'shippingAddress'),
            'dataScope' => 'shippingAddress.vat_id',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'sortOrder' => isset($originalLayout['sortOrder']) ? $originalLayout['sortOrder'] : 120,
            'validation' => $formFieldValidationAtCheckout
        ];
        $fieldLayout = array_merge($originalLayout, $fieldLayout);

        //Magestore_OneStepCheckout @deprecated
        if ($this->moduleManager->isEnabled('Magestore_OneStepCheckout')
           && $this->configHelper->getConfig('onestepcheckout/general/active') == true
        ) {
            $fieldLayout['component'] = 'Geissweb_Euvat/js/form/element/vat-number-magestore-opc';
            $fieldLayout['config']['template'] = 'Geissweb_Euvat/vatfield-magestore';
        }

        //Mageplaza_Osc
        if ($this->moduleManager->isEnabled('Mageplaza_Osc')
            && $this->configHelper->getConfig('osc/general/enabled') == true
        ) {
            $fieldLayout['component'] = 'Geissweb_Euvat/js/form/element/vat-number-mageplaza';
            $fieldLayout['config'] = $this->configHelper->getVatFieldConfigMageplaza($configParam);
        }

        //Add field to shipping address
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['vat_id'] = $fieldLayout;

        $this->logger->debug(var_export($jsLayout['components']['checkout']['children']['steps']
        ['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['vat_id'], true));

        //Add field to billing addresses
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'])
        ) {
            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                     ['payment']['children']['payments-list']['children'] as $paymentGroup => $groupConfig) {
                if ($paymentGroup !== 'before-place-order'
                    && $paymentGroup !== 'paypal-captcha'
                ) {
                    $scopeName = str_replace("-form", '', $paymentGroup);
                    $fieldLayout['config'] = $this->configHelper->getVatFieldConfig(
                        $configParam,
                        'billingAddress' . $scopeName
                    );
                    $fieldLayout['dataScope'] = 'billingAddress' . $scopeName . '.vat_id';
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$paymentGroup]['children']
                    ['form-fields']['children']['vat_id'] = $fieldLayout;
                }
            }
        }

        //Add field to billing address when address is set to be shown on "Payment Page" instead of "Payment Method"
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']
            ['children']['vat_id'])
        ) {
            $billingFieldLayout = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']
            ['form-fields']['children']['vat_id'];

            $billingConfigParam = isset($billingFieldLayout['config']) ? $billingFieldLayout['config'] : [];
            $billingFieldLayout = [
                'component' => 'Geissweb_Euvat/js/form/element/vat-number-co',
                'config' => $this->configHelper->getVatFieldConfig($billingConfigParam, 'billingAddressshared'),
                'dataScope' => 'billingAddressshared.vat_id',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => $formFieldValidationAtCheckout
            ];
            $billingFieldLayout = array_merge($billingFieldLayout, $billingFieldLayout);
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']
            ['children']['vat_id'] = $billingFieldLayout;

            $this->logger->debug(var_export($jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']
            ['children']['form-fields']['children']['vat_id'], true));
        }

        if (isset($formFieldValidationAtCheckout['valid-vat-required'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['additional-payment-validators']['children']['vat-id-required-validator'] = [
                'component' => 'Geissweb_Euvat/js/view/vat-validators'
            ];
        }

        return $jsLayout;
    }
}