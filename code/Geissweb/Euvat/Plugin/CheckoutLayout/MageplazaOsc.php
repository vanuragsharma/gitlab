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
use Magento\Framework\Module\Manager;

/**
 * Class CheckoutLayout
 *
 * Is currently not in use because the form field validation is not working properly for the billing address
 */
class MageplazaOsc
{
    /**
     * @var Configuration
     */
    public $configHelper;

    /**
     * @var Logger
     */
    public $logger;
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * CheckoutLayout constructor.
     *
     * @param Configuration $config
     * @param Logger        $logger
     * @param Manager       $moduleManager
     */
    public function __construct(
        Configuration $config,
        Logger $logger,
        Manager $moduleManager
    ) {
        $this->configHelper = $config;
        $this->logger = $logger;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Mageplaza\Osc\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array $jsLayout
     */
    public function afterProcess(
        \Mageplaza\Osc\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        // Check if module is enabled
        if (!$this->moduleManager->isEnabled('Mageplaza_Osc')
            || $this->configHelper->getConfig('osc/general/enabled') != true
        ) {
            return $jsLayout;
        }

        //$mageplazaLayout = $jsLayout['components']['checkout']['children']['steps']['children'];
        //$this->logger->debug("mageplazaLayout: " . var_export($mageplazaLayout, true));

        $originalLayout = [];
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['billingAddress']['children']['billing-address-fieldset']['children']['vat_id'])
        ) {
            $originalLayout = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['billingAddress']['children']['billing-address-fieldset']['children']['vat_id'];
            $this->logger->debug("OriginalLayout: " . var_export($originalLayout, true));
        }

        $configParam = isset($originalLayout['config']) ? $originalLayout['config'] : [];
        $fieldLayout = [
            'label' => __('VAT Number'),
            'component' => 'Geissweb_Euvat/js/form/element/vat-number-mageplaza',
            'config' => $this->configHelper->getVatFieldConfigMageplaza($configParam, 'billingAddress'),
            'visible' => true,
            //'sortOrder' => 120,
            'validation' => $this->configHelper->getFieldValidationAtCheckout()
        ];
        $fieldLayout = array_merge($originalLayout, $fieldLayout);
        $this->logger->debug("Merged Layout: " . var_export($fieldLayout, true));

        //Add field to shipping address
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['billingAddress']['children']['billing-address-fieldset']['children']['vat_id'] = $fieldLayout;

        //$this->logger->debug(var_export($jsLayout['components']['checkout']['children']['steps']['children']
        //['shipping-step']['children']['billingAddress']['children']['billing-address-fieldset'], true));

        return $jsLayout;
    }
}