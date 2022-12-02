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
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Checkout\Model\Session;

class MoveToWishlist implements ObserverInterface
{
    /**
     * __construct function
     *
     * @param Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param Magento\Checkout\Model\Cart $cart
     * @param \Webkul\ShowPriceAfterLogin\Helper\Data $helper
     * @param \Magento\Catalog\Model\ProductFactory $productModel
     * @param  \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session $session
     */

    public function __construct(
        WishlistProviderInterface $wishlistProvider,
        CheckoutCart $cart,
        \Webkul\ShowPriceAfterLogin\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productModel,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $session
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->cart = $cart;
        $this->helper = $helper;
        $this->productModel = $productModel;
        $this->messageManager = $messageManager;
        $this->session = $session;
    }
    /**
     * Observer to Move The products,Which Are not Allowed by the Module From Cart to WishList .
     * Using controller_action_predispatch event.
     */
    public function execute(Observer $observer)
    {
        $helper = $this->helper;
        $enableOrDisableStoreField = $helper->storeAvilability();
        $productAllowedCategories=$helper->getListOfCategories();
        $enableOrDisableCategory=$helper->statusOfCategorySettingForAllUser();
        $wishlist = $this->wishlistProvider->getWishlist();
        $items = $this->cart->getQuote()->getAllVisibleItems();
        $isAllowedCustomerGroups = $this->helper->isAllowedCustomerGroups();
        if ($enableOrDisableStoreField) {
            if (!$isAllowedCustomerGroups) {
                foreach ($items as $item) {
                    $productId = $item->getProduct()->getId();
                    $productModel = $this->productModel->create()->load($productId);
                    $productCategories=$item->getProduct()->getCategoryIds();
                    $result = array_intersect(explode(',', $productAllowedCategories), $productCategories);
                    $buyRequest = $item->getBuyRequest();
                    $itemId = $item->getItemId();
                    if ((!$enableOrDisableCategory || empty($result)) && (!($productModel->getData('show_price') &&
                    $helper->configPriority() == "product_configuration")
                     )) {
                        $wishlist->addNewItem($productId, $buyRequest);
                        $this->cart->removeItem($itemId);
                        $this->messageManager->addNotice(__(
                            "Your %1 item(s) are moved to Wishlist!",
                            $productModel->getName()
                        ));
                   
                    }
                
                }
                $this->cart->setTotalsCollectedFlag(false);
                $this->cart->save();
           
            }
        }
    }
}
