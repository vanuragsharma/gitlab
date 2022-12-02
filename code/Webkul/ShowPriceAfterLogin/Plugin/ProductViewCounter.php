<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ShowPriceAfterLogin
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c)   Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ShowPriceAfterLogin\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;

class ProductViewCounter
{

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * @var \Webkul\ShowPriceAfterLogin\Helper\Data
     */
    private $helper;

    /**
     * __construct function
     *
     * @param \Webkul\ShowPriceAfterLogin\Helper\Data   $helper
     * @param \Magento\Framework\UrlInterface           $urlInterface
     */
    public function __construct(
        \Webkul\ShowPriceAfterLogin\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlInterface,
        StoreManager $storeManager,
        ?ScopeConfigInterface $scopeConfig = null,
        SerializerInterface $serialize,
        Registry $registry
    ) {
    
        $this->helper = $helper;
        $this->urlInterface = $urlInterface;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->serialize = $serialize;
        $this->registry = $registry;
    }

    /**
     * function to run to change the return data of GetProductPrice.
     *
     * @param \Magento\CatalogSearch\Block\Result $list
     * @param Closure                             $proceed
     * @param Magento\Catalog\Model\Product       $product
     *
     * @return html
     */
    public function aroundGetCurrentProductData(\Magento\Catalog\Block\Ui\ProductViewCounter $productViewCounter, $proceed)
    {
        $productsScope = $this->scopeConfig->getValue(
            'catalog/recently_products/scope',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        $product = $this->registry->registry('product');
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $currentProductData = [
            'items' => null,
            'store' => $store->getId(),
            'currency' => $store->getCurrentCurrency()->getCode(),
            'productCurrentScope' => $productsScope
        ];
        if (!$this->helper->storeAvilability()) {
            return $proceed();
        }
        if ($this->helper->isAllowedForGuestUser($product)) {
            return $proceed();
        }
        $productCustomerGroup = $product->getData('show_price_customer_group');
        if ($product->getData('show_price') && $this->helper->configPriority() == "product_configuration") {
            if ($this->helper->isCustomerLoggedIn()) {
                if ($this->helper->isAllowedCustomerGroupsForParticularProduct($productCustomerGroup)) {
                    return $proceed();
                } else {
                    return $this->serialize->serialize($currentProductData);
                }
            } else {
                return $this->serialize->serialize($currentProductData);
            }
        } else {
            if (($this->helper->storeAvilability() && !$this->helper->isCustomerLoggedIn())) {
                return $this->serialize->serialize($currentProductData);
            } else {
                if ($this->helper->isAllowedCustomerGroups()) {
                        return $proceed();
                } else {
                    return $this->serialize->serialize($currentProductData);
                }
            }
        }
    }
}
