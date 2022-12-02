<?php
namespace Webkul\ShowPriceAfterLogin\CustomerData\Rewrite\Wishlist;

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ShowPriceAfterLogin
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/**
 * Cart source
 */
class Wishlist extends \Magento\Wishlist\CustomerData\Wishlist
{
    /**
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Sidebar $block
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param \Magento\Framework\App\ViewInterface $view
     */
    public function __construct(
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Wishlist\Block\Customer\Sidebar $block,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\App\ViewInterface $view,
        \Webkul\ShowPriceAfterLogin\Helper\Data $helper
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->block = $block;
        $this->view = $view;
        $this->helper= $helper;
        parent::__construct($wishlistHelper, $block, $imageHelperFactory, $view);
    }

    /**
     * Retrieve wishlist item data
     *
     * @param \Magento\Wishlist\Model\Item $wishlistItem
     * @return array
     */
    protected function getItemData(\Magento\Wishlist\Model\Item $wishlistItem)
    {
        $product = $wishlistItem->getProduct();
        $result = parent::getItemData($wishlistItem);
        $result['is_showPrice'] = false;
        $productCustomerGroup = $product->getData('show_price_customer_group');
        if ($product->getData('show_price') && $this->helper->configPriority() == "product_configuration") {
            if ($this->helper->isCustomerLoggedIn()) {
                $result['is_showPrice'] = true;
                if ($product->getData('call_for_price')) {
                    $label = $this->escapeHtmlEntites($product->getData('call_for_price_label'));
                    if ($label == "") {
                        $label = "Not allowed to view price";
                    }
                    $result['label']= $label;
                    $link  = $product->getData('call_for_price_link');
                    $link = $this->helper->checkUrl($link);
                    if ($link == "") {
                        $link = $this->urlInterface->getUrl('contact');
                    }
                    $result['link']=$link;
                       
                } else {
                    $label = "Logged in customer group not allowed to View Price";
                    $result['label']= $label;
                    $link  = "customer-not-allowed";
                    $result['link']=$link;
                }
            }
        }
        if ($this->helper->isCustomerLoggedIn()) {
            if ($this->helper->isCallForPriceConfigSetting() == 1) {
                $result['is_showPrice'] = true;
                $label = $this->escapeHtmlEntites($this->helper->callForPriceConfigLabel());
                if ($label == "") {
                    $label = "Not allowed to view price";
                }
                        $result['label']= $label;
                        $link = $this->helper->callForPriceConfigLink();
                        $link = $this->helper->checkUrl($link);
                if ($link == "") {
                    $link = $this->urlInterface->getUrl('contact');
                }
                        $result['link']=$link;
            }
        }
        return $result;
    }
    public function escapeHtmlEntites($value)
    {
        return htmlentities(htmlentities($value));
    }
}
