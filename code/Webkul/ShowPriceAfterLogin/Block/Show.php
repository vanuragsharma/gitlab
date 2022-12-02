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
namespace Webkul\ShowPriceAfterLogin\Block;

/**
 * ShowPriceAfterLogin block.
 *
 * @author      Webkul Software
 */
class Show extends \Magento\Framework\View\Element\Template
{
    /**
     * Magento\Framework\Registry.
     *
     * @var [type]
     */
    protected $registry;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Webkul\ShowPriceAfterLogin\Helper\Data
     */
    protected $helper;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param \Webkul\ShowPriceAfterLogin\Helper\Data          $helper
     * @param DateTime                                         $date
     * @param Store                                            $store
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Webkul\ShowPriceAfterLogin\Helper\Data $helper,
        \Magento\Wishlist\Helper\Data $WishListHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
        $this->wishlisthelper=$WishListHelper;
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->urlInterface = $urlInterface;
    }

    /**
     * get storeAvilability that weather it is,
     * enable or disable from configuration.
     *
     * @return int
     */
    public function isStoreAvilable()
    {
        return  $this->helper->storeAvilability();
    }

    /**
     * get the selected group of list from config.
     *
     * @return Array
     */
    public function getGroupLists()
    {
        return  $this->helper->getListsOfCustomerGroupToGrantAcess();
    }

    /**
     * get priority that weather it is configuration
     * setting or individual product setting.
     *
     * @return string
     */
    public function configPriority()
    {
        return  $this->helper->configPriority();
    }

    /**
     * return all the attribute of product related to show price.
     *
     * @return Array
     */
    public function getAllShowPriceAfterLoginModuleAttribute()
    {
        $attribute['show_price'] = isset(
            $this->registry->registry('product')->getData()['show_price']
        )?$this->registry->registry('product')->getData()['show_price']:"";

        $attribute['call_for_price'] = isset(
            $this->registry->registry('product')->getData()['call_for_price']
        )?$this->registry->registry('product')->getData()['call_for_price']:"";

        $attribute['show_price_customer_group'] = isset(
            $this->registry->registry('product')->getData()['show_price_customer_group']
        )?$this->registry->registry('product')->getData()['show_price_customer_group']:"";

        $attribute['call_for_price_label'] = isset(
            $this->registry->registry('product')->getData()['call_for_price_label']
        )?$this->registry->registry('product')
                                             ->getData()['call_for_price_label']:"";
        $attribute['call_for_price_link'] = isset(
            $this->registry->registry('product')->getData()['call_for_price_link']
        )?$this->registry->registry('product')->getData()['call_for_price_link']:"";

        return $attribute;
    }

    /**
     * return the status that logged in customer group and product
     * attribute customer group are same or not?
     *
     * @return int
     */
    public function isAllowedCustomerGroupsForParticularProduct($productCustomerGroupAttribute = null)
    {
        return $this->helper->isAllowedCustomerGroupsForParticularProduct($productCustomerGroupAttribute);
    }

    /**
     * get the call for price label from config.
     *
     * @return string
     */
    public function callForPriceConfigLabel()
    {
        return $this->helper->callForPriceConfigLabel();
    }

    /**
     * getLoginUrl function
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->helper->getLoginUrl();
    }

    /**
     * get the call for price link from config
     *
     * @return string
     */
    public function callForPriceConfigLink()
    {
        return $this->helper->callForPriceConfigLink();
    }

    /**
     * return the status that call for price option from config is set or not
     *
     * @return int
     */
    public function isCallForPriceConfigSetting()
    {
        return $this->helper->isCallForPriceConfigSetting();
    }

    /**
     * get add to cart label
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->isCustomerLoggedIn()) {
            return __('Logged in customer group not allowed to View Price');
        } else {
            return __('Please Login To View Price');
        }
    }

    /**
     * check if customer logged in
     * @return boolean
     */
    public function isCustomerLoggedIn()
    {
        return $this->helper->isCustomerLoggedIn();
    }

    /**
     * getlogged in customer group ID
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->helper->getCustomerGroupId();
    }

    /**
     * get redirection url
     *
     * @return string
     */
    public function setRedirectReferer()
    {
        $this->helper->setRedirectReferer($this->_urlBuilder->getCurrentUrl());
    }

    /**
     * checkUrl function check the url is relative or not ?
     *
     * @param string $link
     * @return string
     */
    public function checkUrl($link)
    {
        return $this->helper->checkUrl($link);
    }

    /**
     * getProduct function get the product model
     *
     * @return Magento/Catalog/Product/Model
     */
    public function getProduct()
    {
        return $this->registry->registry('product');
    }
    public function isAllowedForGuestUser($param)
    {
        return $this->helper->isAllowedForGuestUser($param);
    }
    public function getWishListData()
    {
        $wishlist = $this->wishlisthelper->getWishlist();
        $showPriceInfo = [];
        $showPriceLabel=[];
        $showPriceLink=[];
        $payHtml=[];
        $data=[];
        if (!$this->helper->storeAvilability()) {
            $data = json_encode($data);
            return $data;
        }
        foreach ($wishlist->getItemCollection() as $item) {
            $product = $item->getProduct();
            $productUrl = $product->getProductUrl();
            $productCustomerGroup = $product->getData('show_price_customer_group');
            if ($this->helper->isAllowedCustomerGroupsForParticularProduct($productCustomerGroup)) {
                $showPriceInfo[$productUrl]['ShowPrice']= false;
                continue;
            }
            if ($this->helper->isAllowedCustomerGroups()) {
                $showPriceInfo[$productUrl]['ShowPrice']= false;
                continue;
            }
            if ($product->getData('show_price') && $this->helper->configPriority() == "product_configuration") {
                    $showPriceInfo[$productUrl]['ShowPrice']= true;
                if ($product->getData('call_for_price')) {
                    $label = $this->escapeHtmlEntites($product->getData('call_for_price_label'));
                    if ($label == "") {
                        $label = "Not allowed to view price";
                    }
                    $showPriceLabel[$productUrl]['Label']= $label;
                    $link  = $product->getData('call_for_price_link');
                    $link = $this->helper->checkUrl($link);
                    if ($link == "") {
                        $link = $this->urlInterface->getUrl('contact');
                    }
                    $showPriceLink[$productUrl]['Link']=$link;
                    $payHtml[$productUrl]['html']='<span class="wkshowcallforpriceforwishlist" data-label="'
                    .$label.'" data-link="'.$link.'"></span>';
                           
                } else {
                    $showPriceLabel[$productUrl]['Label'] ="Logged in customer group not allowed to View Price";
                    $showPriceLink[$productUrl]['Link']= "#";
                    $payHtml[$productUrl]['html']='<span class="wkshowcallforpriceforwishlist" data-label="'
                    .$label.'" data-link="'.$link.'"></span>';
                            
                }
            } else {
                if ($this->helper->storeAvilability() && $this->helper->isCustomerLoggedIn()) {
                    if ($this->helper->isCallForPriceConfigSetting() == 1) {
                        $showPriceInfo[$productUrl]['ShowPrice']= true;
                        $label = $this->escapeHtmlEntites($this->helper->callForPriceConfigLabel());
                        if ($label == "") {
                            $label = "Not allowed to view price";
                        }
                        $showPriceLabel[$productUrl]['Label']= $label;
                        $link = $this->helper->callForPriceConfigLink();
                        $link = $this->helper->checkUrl($link);
                        if ($link == "") {
                            $link = $this->urlInterface->getUrl('contact');
                        }
                        $showPriceLink[$productUrl]['Link']=$link;
                        $payHtml[$productUrl]['html']='<span class="wkshowcallforpriceforwishlist" data-label="'
                        .$label.'" data-link="'.$link.'"></span>';
                             
                    } else {
                        $payHtml[$productUrl]['html']='<span class="wkremovepriceandcartforwishlist"></span>';
                    }
                }
            }
            $data = [
                "showPriceInfo" => $showPriceInfo,
                "payHtml" => $payHtml,
                "showPriceLink" => $showPriceLink,
                "showPriceLabel" => $showPriceLabel
            ];
        }
        $data = json_encode($data);
        return $data;
    }
    public function escapeHtmlEntites($value)
    {
        return htmlentities(htmlentities($value));
    }
}
