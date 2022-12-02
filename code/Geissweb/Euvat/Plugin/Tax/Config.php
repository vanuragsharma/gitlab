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

namespace Geissweb\Euvat\Plugin\Tax;

use Geissweb\Euvat\Api\Data\ValidationInterface;
use Geissweb\Euvat\Helper\Configuration;
use Geissweb\Euvat\Helper\Uk\ThresholdCalculator;
use Geissweb\Euvat\Logger\Logger;
use Geissweb\Euvat\Model\System\Config\Source\DynamicShipping;
use Geissweb\Euvat\Model\ValidationRepository;
use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class Config
 * Makes some store configuration variables dynamic
 */
class Config
{
    /**
     * @var Configuration
     */
    public $configHelper;

    /**
     * @var ValidationRepository
     */
    public $validationRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var CartRepositoryInterface
     */
    public $cartRepository;

    /**
     * @var Session
     */
    public $customerSession;

    /**
     * @var int
     */
    public $_customerGroup;

    /**
     * @var array<mixed>
     */
    public $_cartProductDisplayRules;
    /**
     * @var array<mixed>
     */
    public $_catalogProductDisplayRules;
    /**
     * @var array<mixed>
     */
    public $_cartSubtotalDisplayRules;

    /**
     * @var State
     */
    private $appState;
    /**
     * @var Quote
     */
    private $backendSessionQuote;
    /**
     * @var ThresholdCalculator
     */
    private $ukThresholdCalc;

    /**
     * TaxConfig constructor.
     *
     * @param Configuration          $configHelper
     * @param ValidationRepository    $validationRepository
     * @param \Magento\Checkout\Model\Session               $checkoutSession
     * @param Session               $customerSession
     * @param CartRepositoryInterface    $cartRepository
     * @param State                  $appState
     * @param Quote          $backendSessionQuote
     * @param Logger                 $logger
     * @param ThresholdCalculator $ukThresholdCalc
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        Configuration $configHelper,
        ValidationRepository $validationRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        Session $customerSession,
        CartRepositoryInterface $cartRepository,
        State $appState,
        Quote $backendSessionQuote,
        Logger $logger,
        ThresholdCalculator $ukThresholdCalc
    ) {
        $this->configHelper = $configHelper;
        $this->validationRepository = $validationRepository;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
        $this->appState = $appState;
        $this->backendSessionQuote = $backendSessionQuote;
        $this->ukThresholdCalc = $ukThresholdCalc;

        //$this->_customerGroup = $this->customerSession->getCustomer()->getGroupId();
        $this->_customerGroup = $this->customerSession->getCustomerGroupId();
        $this->_catalogProductDisplayRules = $this->configHelper->getCatalogPriceDisplayTypeRules();
        $this->_cartProductDisplayRules = $this->configHelper->getCartProductPriceDisplayTypeRules();
        $this->_cartSubtotalDisplayRules = $this->configHelper->getCartSubtotalPriceDisplayTypeRules();
    }

    /**
     * @param $subject \Magento\Tax\Model\Config
     * @param $result  bool
     *
     * @return bool
     */
    public function afterDisplayCartSubtotalInclTax($subject, $result)
    {
        $this->logger->debug('afterDisplayCartSubtotalInclTax default result: ' . (int)$result);
        $this->logger->debug('afterDisplayCartSubtotalInclTax current group: ' . $this->_customerGroup);
        if (array_key_exists($this->_customerGroup, $this->_cartSubtotalDisplayRules)) {
            $type = $this->_cartSubtotalDisplayRules[$this->_customerGroup];
            $this->logger->debug('afterDisplayCartPricesInclTax found rule: ' . $type . ' (result: ' . (int)$subject::DISPLAY_TYPE_INCLUDING_TAX === $type . ')');
            return $subject::DISPLAY_TYPE_INCLUDING_TAX === $type;
        }
        return $result;
    }

    /**
     * @param $subject \Magento\Tax\Model\Config
     * @param $result  bool
     *
     * @return bool
     */
    public function afterDisplayCartSubtotalExclTax($subject, $result)
    {
        $this->logger->debug('afterDisplayCartSubtotalExclTax default result: ' . (int)$result);
        $this->logger->debug('afterDisplayCartSubtotalExclTax current group: ' . $this->_customerGroup);
        if (array_key_exists($this->_customerGroup, $this->_cartSubtotalDisplayRules)) {
            $type = $this->_cartSubtotalDisplayRules[$this->_customerGroup];
            $this->logger->debug('afterDisplayCartSubtotalExclTax found rule: ' . $type . ' (result: ' . (int)$subject::DISPLAY_TYPE_EXCLUDING_TAX === $type . ')');
            return $subject::DISPLAY_TYPE_EXCLUDING_TAX === $type;
        }
        return $result;
    }

    /**
     * @param $subject \Magento\Tax\Model\Config
     * @param $result  bool
     *
     * @return bool
     */
    public function afterDisplayCartSubtotalBoth($subject, $result)
    {
        $this->logger->debug('afterDisplayCartSubtotalBoth default result: ' . (int)$result);
        $this->logger->debug('afterDisplayCartSubtotalBoth current group: ' . $this->_customerGroup);
        if (array_key_exists($this->_customerGroup, $this->_cartSubtotalDisplayRules)) {
            $type = $this->_cartSubtotalDisplayRules[$this->_customerGroup];
            $this->logger->debug('afterDisplayCartSubtotalExclTax found rule: ' . $type . ' (result: ' . (int)$subject::DISPLAY_TYPE_BOTH === $type . ')');
            return $subject::DISPLAY_TYPE_BOTH === $type;
        }
        return $result;
    }

    /**
     * @param $subject \Magento\Tax\Model\Config
     * @param $result  bool
     *
     * @return bool
     */
    public function afterDisplayCartPricesInclTax($subject, $result)
    {
        $this->logger->debug('afterDisplayCartPricesInclTax default result: ' . (int)$result);
        $this->logger->debug('afterDisplayCartPricesInclTax current group: ' . $this->_customerGroup);
        if (array_key_exists($this->_customerGroup, $this->_cartProductDisplayRules)) {
            $type = $this->_cartProductDisplayRules[$this->_customerGroup];
            $this->logger->debug('afterDisplayCartPricesInclTax found rule: ' . $type);
            return $subject::DISPLAY_TYPE_INCLUDING_TAX === $type;
        }
        return $result;
    }

    /**
     * @param $subject \Magento\Tax\Model\Config
     * @param $result  bool
     *
     * @return bool
     */
    public function afterDisplayCartPricesExclTax($subject, $result)
    {
        $this->logger->debug('afterDisplayCartPricesExclTax default result: ' . (int)$result);
        $this->logger->debug('afterDisplayCartPricesExclTax current group: ' . $this->_customerGroup);
        if (array_key_exists($this->_customerGroup, $this->_cartProductDisplayRules)) {
            $type = $this->_cartProductDisplayRules[$this->_customerGroup];
            $this->logger->debug('afterDisplayCartPricesExclTax found rule: ' . $type);
            return $subject::DISPLAY_TYPE_EXCLUDING_TAX === $type;
        }
        return $result;
    }

    /**
     * @param $subject \Magento\Tax\Model\Config
     * @param $result  bool
     *
     * @return bool
     */
    public function afterDisplayCartPricesBoth($subject, $result)
    {
        $this->logger->debug('afterDisplayCartPricesBoth default result: ' . (int)$result);
        $this->logger->debug('afterDisplayCartPricesBoth current group: ' . $this->_customerGroup);
        if (array_key_exists($this->_customerGroup, $this->_cartProductDisplayRules)) {
            $type = $this->_cartProductDisplayRules[$this->_customerGroup];
            $this->logger->debug('afterDisplayCartPricesBoth found rule: ' . $type);
            return $subject::DISPLAY_TYPE_BOTH === $type;
        }
        return $result;
    }

    /**
     * Dynamic catalog price display
     *
     *  1 - Excluding tax
     *  2 - Including tax
     *  3 - Both
     *
     * @param \Magento\Tax\Model\Config $subject
     * @param int                       $type
     *
     * @return int
     */
    public function afterGetPriceDisplayType(\Magento\Tax\Model\Config $subject, $type)
    {
        $this->logger->debug('afterGetPriceDisplayType current group: ' . $this->_customerGroup);
        if (array_key_exists($this->_customerGroup, $this->_catalogProductDisplayRules)) {
            $this->logger->debug('afterGetPriceDisplayType found rule: ' . $type);
            $type = $this->_catalogProductDisplayRules[$this->_customerGroup];
        }
        $this->logger->debug('afterGetPriceDisplayType return ' . $type);
        return $type;
    }

    /**
     * @param \Magento\Tax\Model\Config $subject
     * @param                           $result
     *
     * @return bool
     */
    public function afterCrossBorderTradeEnabled(\Magento\Tax\Model\Config $subject, $result)
    {
        try {
            if ($result) {
                $basedOn = $this->configHelper->getVatBasedOn();
                //Backend
                if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML) {
                    $this->logger->debug("afterCrossBorderTradeEnabled processing AdminOrder.");
                    $backendQuote = $this->backendSessionQuote->getQuote();
                    if ($basedOn == 'shipping') {
                        $basedOnAddress = $backendQuote->getShippingAddress();
                    } else {
                        $basedOnAddress = $backendQuote->getBillingAddress();
                    }
                    // To have the correct prices in case the group is for including tax
                    if ($this->configHelper->isNoDynamicGroup($backendQuote->getCustomerGroupId())) {
                        return $result;
                    }
                    //Frontend
                } else {
                    // To have the correct prices in case the group is for including tax
                    if ($this->customerSession->isLoggedIn()) {
                        $currentCustomerGroup = $this->customerSession->getCustomer()->getGroupId();
                    } else {
                        $currentCustomerGroup = \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID;
                    }
                    if ($this->configHelper->isNoDynamicGroup($currentCustomerGroup)) {
                        return $result;
                    }

                    $quoteId = $this->checkoutSession->getQuoteId();
                    if (!empty($quoteId)) {
                        $this->logger->debug("afterCrossBorderTradeEnabled processing FrontQuote ID: $quoteId");
                        /** @var \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote $quote */
                        $quote = $this->cartRepository->get($quoteId);
                        if ($basedOn == 'shipping' && !$quote->getIsVirtual()) {
                            $basedOnAddress = $quote->getShippingAddress();
                        } else {
                            $basedOnAddress = $quote->getBillingAddress();
                        }

                        $countryId = $basedOnAddress->getCountryId();
                        if (empty($countryId)) {
                            $this->logger->debug(
                                "afterCrossBorderTradeEnabled did not find countryID from quote address, use default"
                            );
                            if ($basedOn == 'shipping') {
                                $basedOnAddress = $this->customerSession->getCustomer()->getDefaultShippingAddress();
                            } else {
                                $basedOnAddress = $this->customerSession->getCustomer()->getDefaultBillingAddress();
                            }
                        }
                    } else {
                        $this->logger->debug("afterCrossBorderTradeEnabled using default addresses");
                        if ($basedOn == 'shipping') {
                            $basedOnAddress = $this->customerSession->getCustomer()->getDefaultShippingAddress();
                        } else {
                            $basedOnAddress = $this->customerSession->getCustomer()->getDefaultBillingAddress();
                        }
                    }
                }

                if ($basedOnAddress === false || $basedOnAddress === null) {
                    $this->logger->debug("afterCrossBorderTradeEnabled no Address!");
                    return $result;
                }

                $countryId = $basedOnAddress->getCountryId();
                $vatNumber = $basedOnAddress->getVatId();
                if (empty($countryId)) {
                    $this->logger->debug("afterCrossBorderTradeEnabled no CountryID!", $basedOnAddress->debug());
                    return $result;
                }

                $this->logger->debug("afterCrossBorderTradeEnabled isEuCountry($countryId): "
                                     . (int)$this->configHelper->isEuCountry($countryId));

                //Magic starts here
                if ((!$this->configHelper->isEuCountry($countryId) || $this->ukThresholdCalc->isDeliveryToUk())
                    && $this->configHelper->getDisableCbtForOutOfEurope()
                ) {
                    $this->logger->debug("afterCrossBorderTradeEnabled disableCbtForOutOfEurope");
                    $result = false;
                } elseif (!empty($vatNumber) && $this->configHelper->getDisableCbtForEuBusiness()) {
                    /** @var ValidationInterface $validation */
                    $validation = $this->validationRepository->getByVatId($vatNumber);
                    if ($validation && $validation->getVatIsValid()) {
                        $this->logger->debug("afterCrossBorderTradeEnabled disableCbtForEuBusiness");
                        $result = false;
                    }
                }
            }
            $this->logger->debug("afterCrossBorderTradeEnabled result: " . (int)$result);
            return $result;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param \Magento\Tax\Model\Config $subject
     * @param                           $result
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetShippingTaxClass(\Magento\Tax\Model\Config $subject, $result)
    {
        $this->logger->debug("afterGetShippingTaxClass START result: $result");
        $useDynamicShippingTax = $this->configHelper->getUseDynamicShippingTaxClass();
        $defaultProductTaxClass = $this->configHelper->getDefaultProductTaxClass();
        $reducedProductClass = $this->configHelper->getReducedProductTaxClass();
        $reducedShippingClass = $this->configHelper->getReducedShippingTaxClass();
        $superReducedProductClass = $this->configHelper->getSuperReducedProductTaxClass();
        $superReducedShippingClass = $this->configHelper->getSuperReducedShippingTaxClass();

        if ($useDynamicShippingTax > 0 && $reducedProductClass && $reducedShippingClass) {
            $this->logger->debug("afterGetShippingTaxClass useDynamicShippingTax");

            //Backend
            if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML) {
                $this->logger->debug("afterGetShippingTaxClass processing AdminOrder.");
                $items = $this->backendSessionQuote->getQuote()->getAllVisibleItems();
            //Frontend
            } else {
                /**@var \Magento\Quote\Model\Quote\Item[] $items */
                $quoteId = $this->checkoutSession->getQuoteId();
                if (!empty($quoteId)) {
                    $this->logger->debug("afterGetShippingTaxClass processing FrontQuote.");
                    /**@var \Magento\Quote\Model\Quote $quote */
                    $quote = $this->cartRepository->get($quoteId);
                    //$items = $quote->getItems();
                    $items = $quote->getAllItems();
                }
            }

            if (!isset($items)) {
                return $result;
            }

            switch ($useDynamicShippingTax) {
                case DynamicShipping::TYPE_BY_RATE_WITH_LARGEST_TOTAL:
                    $this->logger->debug("afterGetShippingTaxClass TYPE_BY_RATE_WITH_LARGEST_TOTAL");
                    $totals = [];
                    foreach ($items as $item) {
                        if ($item->getProductType() !== 'simple') {
                            continue;
                        }
                        $productTaxClassId = (int)$item->getProduct()->getTaxClassId();
                        $qty = $item->getQty();
                        if ($item->getParentItemId() !== null) { // Use qty of the parent, coz child qty is always 1...
                            $qty = $item->getParentItem()->getQty();
                        }
                        if (isset($totals[$productTaxClassId])) {
                            $totals[$productTaxClassId] += $item->getPrice() * $qty;
                        } else {
                            $totals[$productTaxClassId] = $item->getPrice() * $qty;
                        }
                    }
                    $this->logger->debug("afterGetShippingTaxClass calculated totals:", $totals);

                    arsort($totals);
                    reset($totals);
                    $classToUse = key($totals);

                    if ($classToUse === $reducedProductClass) {
                        $this->logger->debug("afterGetShippingTaxClass reduced: $reducedShippingClass");
                        return $reducedShippingClass;
                    } elseif ($classToUse === $superReducedProductClass) {
                        $this->logger->debug("afterGetShippingTaxClass super reduced: $superReducedShippingClass");
                        return $superReducedShippingClass;
                    }
                    break;

                case DynamicShipping::TYPE_BY_RATE_WITH_HIGHEST_RATE:
                    $this->logger->debug("afterGetShippingTaxClass TYPE_BY_RATE_WITH_HIGHEST_RATE");
                    $classes = [];
                    foreach ($items as $item) {
                        if ($item->getProductType() !== 'simple') {
                            continue;
                        }
                        $classes[] = (int)$item->getProduct()->getTaxClassId();
                    }
                    $this->logger->debug("afterGetShippingTaxClass classes for cart items", $classes);
                    if (in_array($defaultProductTaxClass, $classes)) {
                        $this->logger->debug("afterGetShippingTaxClass using default shipping class");
                        break;
                    }
                    if (in_array($reducedProductClass, $classes)) {
                        $this->logger->debug("afterGetShippingTaxClass using reduced shipping: $reducedShippingClass");
                        return $reducedShippingClass;
                    }
                    if (in_array($superReducedProductClass, $classes)) {
                        $this->logger->debug("afterGetShippingTaxClass super reduced: $superReducedShippingClass");
                        return $superReducedShippingClass;
                    }
                    break;

                case DynamicShipping::TYPE_DEFAULT:
                default:
                    break;
            }
        }
        return $result;
    }
}
