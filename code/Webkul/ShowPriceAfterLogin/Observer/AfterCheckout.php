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

namespace Webkul\ShowPriceAfterLogin\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AfterCheckout implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $quoteItem;

    /**
     * @var \Webkul\ShowPriceAfterLogin\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;
    
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * __construct function
     *
     * @param \Magento\Checkout\Model\Session               $checkoutSession
     * @param \Webkul\ShowPriceAfterLogin\Helper\Data       $helper
     * @param \Magento\Framework\Message\ManagerInterface   $messageManager
     * @param \Magento\Checkout\Model\Cart                  $cart
     * @param \Magento\Framework\UrlInterface               $url
     * @param \Magento\Quote\Model\Quote\Item               $quoteItem
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\ShowPriceAfterLogin\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\UrlInterface $url,
        \Magento\Quote\Model\Quote\Item $quoteItem
    ) {
  
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->url = $url;
        $this->cart = $cart;
        $this->quoteItem = $quoteItem;
    }

    /**
     * Observer to stop from checkout if customer is logged out or not allowed.
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->storeAvilability()) {
            return true;
        }
        $flag = 0;
        foreach ($this->cart->getQuote()->getItemsCollection() as $item) {
            if ($this->helper->statusOfCategorySettingForAllUser()
                && $this->helper->isAllowedForGuestUser($item->getProduct())
            ) {
                continue;
            } else {
                    $productModel = $item->getProduct();
                    $showPriceCustomerGroup = $productModel->getShowPriceCustomerGroup();
                if ($productModel->getData('show_price') &&
                    $this->helper->configPriority() == "product_configuration"
                ) {
                    $isAllowedCustomerGroups = $this->helper->isAllowedCustomerGroupsForParticularProduct(
                        $showPriceCustomerGroup
                    );
                } else {
                    $isAllowedCustomerGroups = $this->helper->isAllowedCustomerGroups();
                }
                if ($this->helper->storeAvilability() && !$isAllowedCustomerGroups) {
                    $flag = 1;
                    $this->messageManager->addError(__(
                        "Login customer not allowed to purchase %1 items!",
                        $productModel->getName()
                    ));
                }
            }
        }
        if ($flag) {
            $CustomRedirectionUrl = $this->url->getUrl('/');
            $observer->getControllerAction()
                ->getResponse()
                ->setRedirect($CustomRedirectionUrl);
        }
    }
    /**
     * getCheckoutSession function get the object of checkout session
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * getItemModel function get the object of the quote
     *
     * @return \Magento\Quote\Model\Quote\Item
     */
    public function getItemModel()
    {
        return $this->quoteItem;
    }
}
