<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ShowPriceAfterLogin
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ShowPriceAfterLogin\Plugin\Catalog\Search;

class ProductList
{

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;
     
    /**
     * @var \Webkul\ShowPriceAfterLogin\Helper\Data
     */
    protected $helper;

    /**
     * __construct function
     *
     * @param \Magento\Framework\UrlInterface           $urlInterface
     * @param \Webkul\ShowPriceAfterLogin\Helper\Data   $helper
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface,
        \Webkul\ShowPriceAfterLogin\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->urlInterface = $urlInterface;
    }

    /**
     * function to run to change the retun data of GetProductPrice.
     *
     * @param \Magento\CatalogSearch\Block\Result $list
     * @param Closure                             $proceed
     * @param Magento\Catalog\Model\Product       $product
     *
     * @return html
     */
    public function aroundGetProductPrice(\Magento\CatalogSearch\Block\Result $list, $proceed, $product)
    {

        if (!$this->helper->storeAvilability()) {
            return $proceed($product);
        }

        if ($this->helper->isAllowedForGuestUser($product)) {
            return $proceed($product);
        }
        $productCustomerGroup = $product->getData('show_price_customer_group');
        if ($product->getData('show_price') && $this->helper->configPriority() == "product_configuration") {
            if ($this->helper->isCustomerLoggedIn()) {
                if ($this->helper->isAllowedCustomerGroupsForParticularProduct($productCustomerGroup)) {
                    return $proceed($product);
                } else {
                    if ($product->getData('call_for_price')) {
                        $label = $this->escapeHtmlEntites($product->getData('call_for_price_label'));
                        if ($label == "") {
                            $label = "Not allowed to view price";
                        }
                        $link  = $product->getData('call_for_price_link');
                        
                        $link = $this->helper->checkUrl($link);
                        if ($link == "") {
                            $link = $this->urlInterface->getUrl('contact');
                        }
                        return '<span class="wkshowcallforprice" data-label="'
                        .$label.'" data-link="'.$link.'"></span>';
                    } else {
                        $label = "Logged in customer group not allowed to View Price";
                        $link  = "customer-not-allowed";
                        return '<span class="wkshowcallforprice" data-label="'
                        .$label.'" data-link="'.$link.'"></span>';
                    }
                }
            } else {
                if ($product->getData('call_for_price')) {
                    $label = $this->escapeHtmlEntites($product->getData('call_for_price_label'));
                    if ($label == "") {
                        $label = "Not allowed to view price";
                    }
                    $link  = $product->getData('call_for_price_link');
                    $link = $this->helper->checkUrl($link);
                    if ($link == "") {
                        $link = $this->urlInterface->getUrl('contact');
                    }
                    return '<span class="wkshowcallforprice" data-label="'.$label.'" data-link="'.$link.'"></span>';
                } else {
                    $label = 'Please Login To View Price';
                    $link  = "log-in";
                    return '<span class="wkshowcallforprice" data-label="'.$label.'" data-link="'.$link.'"></span>';
                }
            }
        } else {
            if (($this->helper->storeAvilability() && !$this->helper->isCustomerLoggedIn())) {
                if ($this->helper->isCallForPriceConfigSetting() == 1) {
                    $label = $this->escapeHtmlEntites($this->helper->callForPriceConfigLabel());
                    if ($label == "") {
                        $label = "Not allowed to view price";
                    }
                    $link = $this->helper->callForPriceConfigLink();
                    $link = $this->helper->checkUrl($link);
                    if ($link == "") {
                        $link = $this->urlInterface->getUrl('contact');
                    }
                    return '<span class="wkshowcallforprice" data-label="'.$label.'" data-link="'.$link.'"></span>';
                } else {
                    return '<span style="display:block;margin:10px 0" class="wkremovepriceandcart"></span>';
                }
            } else {
                if ($this->helper->isAllowedCustomerGroups()) {
                        return $proceed($product);
                } else {
                    if ($this->helper->isCallForPriceConfigSetting() == 1) {
                        $label = $this->escapeHtmlEntites($this->helper->callForPriceConfigLabel());
                        if ($label == "") {
                            $label = "Not allowed to view price";
                        }
                        $link = $this->helper->callForPriceConfigLink();
                        $link = $this->helper->checkUrl($link);
                        if ($link == "") {
                            $link = $this->urlInterface->getUrl('contact');
                        }
                        return '<span class="wkshowcallforprice" data-label="'
                        .$label.'" data-link="'.$link.'"></span>';
                    } else {
                        return '<span class="wkremovepriceandcart"></span>';
                    }
                }
            }
        }
    }
    
    /**
     * escapeHtmlEntites function escape the html tags in a string
     *
     * @param string $value
     * @return string
     */
    public function escapeHtmlEntites($value)
    {
        return htmlentities(htmlentities($value));
    }
}
