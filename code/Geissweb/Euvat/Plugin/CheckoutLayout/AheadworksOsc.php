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

/**
 * Checkout Layout Modifier for AW Checkout
 */
class AheadworksOsc
{
    /**
     * @var \Geissweb\Euvat\Helper\Configuration
     */
    public $configHelper;

    /**
     * @var \Geissweb\Euvat\Logger\Logger
     */
    public $logger;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    public $serializer;

    /**
     * CheckoutLayout constructor.
     *
     * @param \Geissweb\Euvat\Helper\Configuration             $config
     * @param \Geissweb\Euvat\Logger\Logger                    $logger
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Geissweb\Euvat\Helper\Configuration $config,
        \Geissweb\Euvat\Logger\Logger $logger,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->configHelper = $config;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @param \Aheadworks\OneStepCheckout\Block\Checkout $subject
     * @param                                            $originalJsLayout
     *
     * @return string $jsLayout
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetJsLayout(
        \Aheadworks\OneStepCheckout\Block\Checkout $subject,
        $originalJsLayout
    ) {

        $jsLayout = $this->serializer->unserialize($originalJsLayout);
        //$this->logger->debug("Aheadworks complete OriginalLayout: ".var_export($jsLayout, true));
        $originalFieldLayout = [];
        $keyForVatField = null;

        if (isset($jsLayout['components']['checkout']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'])
        ) {
            foreach ($jsLayout['components']['checkout']['children']['shippingAddress']['children']
                ['shipping-address-fieldset']['children'] as $key => $fieldsetChild) {
                $this->logger->debug('checking '.$key);
                if (isset($fieldsetChild['children']['vat_id'])) {
                    $keyForVatField = $key;
                    $originalFieldLayout = $fieldsetChild['children']['vat_id'];
                    //$this->logger->debug("Aheadworks OriginalShippingLayout: ".var_export($originalFieldLayout, true));
                }
            }
        }
        if ($keyForVatField === null) {
            return $originalJsLayout;
        }

        $configParam = isset($originalFieldLayout['config']) ? $originalFieldLayout['config'] : [];
        $fieldLayout = [
            'label' => __('VAT Number'),
            'component' => 'Geissweb_Euvat/js/form/element/vat-number-aheadworks',
            'config' => $this->configHelper->getVatFieldConfigAheadworks($configParam, 'shippingAddress'),
            'dataScope' => 'shippingAddress.vat_id',
            'visible' => true,
            'sortOrder' => 120,
            'validation' => $this->configHelper->getFieldValidationAtCheckout()
        ];
        $fieldLayout = array_merge($originalFieldLayout, $fieldLayout);
        $jsLayout['components']['checkout']['children']['shippingAddress']['children']
        ['shipping-address-fieldset']['children'][$keyForVatField]['children']['vat_id'] = $fieldLayout;


        //Add to billing address
        $originalBillingFieldLayout = [];
        $keyForBillingVatField = null;
        if (isset($jsLayout['components']['checkout']['children']['paymentMethod']['children']['billingAddress']['children']
            ['billing-address-fieldset']['children'])) {
            foreach ($jsLayout['components']['checkout']['children']['paymentMethod']['children']['billingAddress']['children']
                ['billing-address-fieldset']['children'] as $key => $fieldsetChild) {
                $this->logger->debug('checking '.$key);
                if (isset($fieldsetChild['children']['vat_id'])) {
                    $keyForBillingVatField = $key;
                    $originalBillingFieldLayout = $fieldsetChild['children']['vat_id'];
                    //$this->logger->debug("Aheadworks OriginalBillingLayout: ".var_export($originalBillingFieldLayout, true));
                }
            }
        }

        if ($keyForBillingVatField !== null) {
            $configParam = isset($originalBillingFieldLayout['config']) ? $originalBillingFieldLayout['config'] : [];
            $fieldLayout = [
                'label' => __('VAT Number'),
                'component' => 'Geissweb_Euvat/js/form/element/vat-number-aheadworks',
                'config' => $this->configHelper->getVatFieldConfigAheadworks($configParam, 'billingAddress'),
                'dataScope' => 'billingAddress.vat_id',
                'visible' => true,
                'sortOrder' => 120,
                'validation' => $this->configHelper->getFieldValidationAtCheckout()
            ];
            $billingFieldLayout = array_merge($originalBillingFieldLayout, $fieldLayout);
            $jsLayout['components']['checkout']['children']['paymentMethod']['children']['billingAddress']['children']
            ['billing-address-fieldset']['children'][$keyForBillingVatField]['children']['vat_id'] = $billingFieldLayout;
        }

        $this->logger->debug(var_export($jsLayout, true));
        return $this->serializer->serialize($jsLayout);
        //return \Zend_Json::encode($jsLayout);
    }
}