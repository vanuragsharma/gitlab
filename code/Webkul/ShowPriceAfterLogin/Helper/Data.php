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
namespace Webkul\ShowPriceAfterLogin\Helper;

/**
 * ShowPriceAfterLogin data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ENABLE_DISABLE_STORE_FIELD = 'webkulShowPriceAfterLogin/storeAbility/enableOrDisableStoreField';
    const CUSTOMER_GROUP_LIST = 'webkulShowPriceAfterLogin/storeAbility/customerGroupList';
    const CALL_PRICE_ENABLE ='webkulShowPriceAfterLogin/callForPrice/callForPriceEnable';
    const CALL_PRICE_TITLE ='webkulShowPriceAfterLogin/callForPrice/callForPriceTitle';
    const CALL_PRICE_LINK='webkulShowPriceAfterLogin/callForPrice/callForPriceLink';
    const ENABLE_DISABLE_CATEGORY='webkulShowPriceAfterLogin/storeAbility/enableOrDisableCategory';
    const CATEGORY_LIST='webkulShowPriceAfterLogin/storeAbility/categoryList';
    const CALL_PRICE_CONFIG_PRIORITY_ENABLE=
    'webkulShowPriceAfterLogin/callForPriceConfigPriority/callForPriceConfigPriorityEnable';
    /**
     * Customer session.
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Customer session.
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * Customer session.
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $productFactory;

    /**
     * @var \Magento\Customer\Model\Customer\Attribute\Source\Group
     */
    protected $customerAttrGrp;

    /**
     * __construct function
     *
     * @param Session                                                   $customerSession
     * @param \Magento\Framework\App\Helper\Context                     $context
     * @param \Magento\Catalog\Model\ProductFactory                     $productFactory
     * @param \Magento\Customer\Model\Customer\Attribute\Source\Group   $customerAttrGrp
     */
    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\Customer\Attribute\Source\Group $customerAttrGrp
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        $this->urlInterface = $context->getUrlBuilder();
        $this->productFactory = $productFactory;
        $this->customerAttrGrp = $customerAttrGrp;
        parent::__construct($context);
    }

    /**
     * Return Customer id.
     *
     * @return bool|0|1
     */
    public function getCustomerId()
    {
        return $this->customerSessionFactory->create()->getCustomerId();
    }

    /**
     * Return Customer id.
     *
     * @return bool|0|1
     */
    public function getCustomerGroupId()
    {
        return $this->customerSessionFactory->create()->getCustomer()->getGroupId();
    }

    /**
     * Return Customer id.
     *
     * @return bool|0|1
     */
    public function isCustomerLoggedIn()
    {
        return $this->customerSessionFactory->create()->isLoggedIn();
    }

    /**
     * get redirection url
     *
     * @return string
     */
    public function setRedirectReferer($url)
    {
        $this->customerSessionFactory->create()->setBeforeAuthUrl($url);
    }

    /**
     * get storeAvilability that weather it is,
     * enable or disable from configuration.
     *
     * @return int
     */
    public function storeAvilability()
    {
        return $this->scopeConfig
                    ->getValue(
                        self::ENABLE_DISABLE_STORE_FIELD,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
    }

    /**
     * return the allowed customer group from config to see the price of product
     *
     * @return int
     */
    public function getListsOfCustomerGroupToGrantAcess()
    {
        return $this->scopeConfig
                    ->getValue(
                        self::CUSTOMER_GROUP_LIST,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
    }

    /**
     * return the status that call for price option from config is set or not
     *
     * @return int
     */
    public function isAllowedCustomerGroups()
    {
        $listOfGroupsFromConfig = $this->getListsOfCustomerGroupToGrantAcess();
        $loggedInUserGroup =  $this->customerSessionFactory->create()->getCustomer()->getGroupId();
        if (in_array($loggedInUserGroup, explode(',', $listOfGroupsFromConfig))) {
            return 1;
        }
        return 0;
    }
    public function isCallForPriceConfigSetting()
    {
        return $this->scopeConfig
            ->getValue(
                self::CALL_PRICE_ENABLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * get the call for price label from config.
     *
     * @return string
     */
    public function callForPriceConfigLabel()
    {
        return $this->scopeConfig
            ->getValue(
                self::CALL_PRICE_TITLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * get the call for price link from config
     *
     * @return string
     */
    public function callForPriceConfigLink()
    {
        return $this->scopeConfig
            ->getValue(
                self::CALL_PRICE_LINK,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * statusOfCategorySettingForAllUser function get the status of category setting for all user
     *
     * @return boolean
     */
    public function statusOfCategorySettingForAllUser()
    {
        return $this->scopeConfig
            ->getValue(
                self::ENABLE_DISABLE_CATEGORY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * getListOfCategories function get the list of categories allowed from backend for all user
     *
     * @return array
     */
    public function getListOfCategories()
    {
        return $this->scopeConfig
            ->getValue(
                self::CATEGORY_LIST,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * isAllowedForGuestUser function check weather the category of product is allowed
     * for selected categories from backend
     *
     * @param [Magento/Catlog/Product/Model] $product
     * @return boolean
     */
    public function isAllowedForGuestUser($product = null)
    {
        if ($this->statusOfCategorySettingForAllUser()) {
            $allowedCategoriesLists = $this->getListOfCategories();
            if (strpos($allowedCategoriesLists, 'all cat') !== false) {
                return true;
            }
            $cat_id = $product->getCategoryIds();
            $result = array_intersect(explode(',', $allowedCategoriesLists), $cat_id);
            if (!empty($result)) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    /**
     * return the status that logged in customer group and product
     * attribute customer group are same or not?
     *
     * @return int
     */
    public function isAllowedCustomerGroupsForParticularProduct($productCustomerGroupAttribute)
    {
        $array = explode(',', $productCustomerGroupAttribute);
        $loggedInUserGroup =  $this->customerSessionFactory->create()->getCustomer()->getGroupId();
        if (in_array($loggedInUserGroup, $array)) {
            return 1;
        }
        return 0;
    }

    /**
     * get all list of group attribute of customer to show on particular product page
     *
     * @return Array
     */
    public function getGroupsLists()
    {
        $groupOptions = $this->customerAttrGrp->getAllOptions();
        return $groupOptions;
    }

    /**
     * get priority that weather it is configuration
     * setting or individual product setting.
     *
     * @return string
     */
    public function configPriority()
    {
        return $this->scopeConfig
                    ->getValue(
                        self::CALL_PRICE_CONFIG_PRIORITY_ENABLE,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
    }

    /**
     * checkUrl function check the link is relative url or not
     *
     * @param string $link
     * @return string
     */
    public function checkUrl($link)
    {
        if ($link == 'customer/account/login' || $link == 'customer/account/login') {
            return $this->getLoginUrl();
        }
        if ($link == "") {
            return "";
        }
        $urlParts = \Zend\Uri\UriFactory::factory($link);
        if ($urlParts->getScheme() !== null &&
        ($urlParts->getScheme() == 'http' || $urlParts->getScheme() == 'https') &&
        $urlParts->getHost() !== null
        ) {
            return $link;
        } else {
            return $this->urlInterface->getUrl($link);
        }
    }

    /**
     * getLoginUrl function
     *
     * @return string
     */
    public function getLoginUrl()
    {
        $login_url = $this->urlInterface->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $url = $this->urlInterface->getUrl('customer/account/login', ['referer' => base64_encode($login_url)]);
        return $url;
    }

    /**
     * checkPriceEnableAtWish function
     *
     * @param integer $productId
     * @return bool
     */
    public function checkPriceEnableAtWish($productId = 0)
    {
        if ($this->statusOfCategorySettingForAllUser()) {
            $product = $this->productFactory->create()->load($productId);
            $allowedCategoriesLists = $this->getListOfCategories();
            if (strpos($allowedCategoriesLists, 'all cat') !== false) {
                return true;
            }
            $cat_id = $product->getCategoryIds();
            $result = array_intersect(explode(',', $allowedCategoriesLists), $cat_id);
            if (!empty($result)) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    /**
     * getProductShowPriceAfterLoginData function
     *
     * @param int $productId
     * @return array
     */
    public function getProductShowPriceAfterLoginData($productId = null)
    {
        $proMod = $this->productFactory->create();
        $proMod->load($productId);
        $data = [];
        $data['show_price'] = $proMod->getShowPrice();
        $data['call_for_price'] = $proMod->getCallForPrice();
        $data['show_price_customer_group'] = $proMod->getShowPriceCustomerGroup();
        $data['call_for_price_label'] = $proMod->getCallForPriceLabel();
        $data['call_for_price_link'] = $proMod->getCallForPriceLink();
        return $data;
    }
}
