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
use Magento\Framework\Exception\LocalizedException;

class CartAddAfter implements ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Webkul\ShowPriceAfterLogin\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalaogProduct;
    
    /**
     * __construct function
     *
     * @param \Psr\Log\LoggerInterface                  $logger
     * @param \Webkul\ShowPriceAfterLogin\Helper\Data   $helper
     * @param \Magento\Catalog\Model\ProductFactory     $catalaogProduct
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Webkul\ShowPriceAfterLogin\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $catalaogProduct
    ) {
    
        $this->logger = $logger;
        $this->helper = $helper;
        $this->catalaogProduct = $catalaogProduct;
    }

    /**
     * Observer to stop product from adding to cart.
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $productId = $item->getProductId();
        $productModel = $this->catalaogProduct->create()->load($productId);
        if (!$this->helper->storeAvilability()) {
            return true;
        }
        if ($this->helper->isAllowedForGuestUser($productModel)) {
            return true;
        }
        $showPriceCustomerGroup = $productModel->getShowPriceCustomerGroup();
        if ($productModel->getData('show_price') && $this->helper->configPriority() == "product_configuration") {
            $isAllowedCustomerGroups = $this->helper->isAllowedCustomerGroupsForParticularProduct(
                $showPriceCustomerGroup
            );
        } else {
            $isAllowedCustomerGroups = $this->helper->isAllowedCustomerGroups();
        }
        if ($productId) {
            $status = $this->helper->isCustomerLoggedIn();
            if ($this->helper->storeAvilability()) {
                if (!$status || !$isAllowedCustomerGroups) {
                    if (!$status) {
                        try {
                            throw new LocalizedException(__('Please login to purchase items'));
                        } catch (Exception $e) {
                            $this->logger->critical($e);
                        }
                    }
                    if ($status && !$isAllowedCustomerGroups) {
                        try {
                            throw new LocalizedException(__('Login customer not allowed to purchase items!'));
                        } catch (Exception $e) {
                            $this->logger->critical($e);
                        }
                    }
                }
            }
        }
    }
}
