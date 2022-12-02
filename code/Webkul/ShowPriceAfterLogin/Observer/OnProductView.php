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

class OnProductView implements ObserverInterface
{

    /**
     * @var \Webkul\ShowPriceAfterLogin\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productFactory;

    /**
     * __construct function
     *
     * @param \Webkul\ShowPriceAfterLogin\Helper\Data   $helper
     * @param \Magento\Framework\Registry               $registry
     * @param \Magento\Catalog\Model\ProductFactory     $productFactory
     */
    public function __construct(
        \Webkul\ShowPriceAfterLogin\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
    
        $this->helper = $helper;
        $this->registry = $registry;
        $this->productFactory = $productFactory;
    }

    /**
     * Observer to disable product price if customer is logged out .
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->storeAvilability()) {
            return true;
        }
        $status = $this->helper->isCustomerLoggedIn();
        $callForPriceAttributes = $this->getModuleAttributeForParticularProduct();
        if (is_array($callForPriceAttributes) && !empty($callForPriceAttributes)) {
            $product = $this->productFactory->create()->load($callForPriceAttributes['product_id']);
            $isAllowedForGuest = $this->helper->isAllowedForGuestUser($product);
            $isAllowedCustomerGroups = $this->helper->isAllowedCustomerGroups();
            $productCustomerGroup = $this->helper->isAllowedCustomerGroupsForParticularProduct(
                $callForPriceAttributes['show_price_customer_group']
            );
            if ($callForPriceAttributes['show_price'] == 1
                && $this->helper->configPriority() == "product_configuration"
            ) {
                if (!$status || !$productCustomerGroup) {
                    $block = $observer->getBlock();
                    if ($block->getType() == \Magento\Catalog\Pricing\Render::class
                        || $block->getType() == \Magento\Framework\Pricing\Render::class) {
                        $block->setTemplate(false);
                    }
                }
            } else {
                if ($this->helper->storeAvilability()) {
                    if ((!$status || !$isAllowedCustomerGroups) && !$isAllowedForGuest) {
                        $block = $observer->getBlock();
                        if ($block->getType() == \Magento\Catalog\Pricing\Render::class
                            || $block->getType() == \Magento\Framework\Pricing\Render::class) {
                            $block->setTemplate(false);
                        }
                    }
                }
            }
        }
    }

    /**
     * getAllShowPriceAfterLoginModuleAttribute function return the aatribute
     * value related to ShowPrixeAfterLogin module
     * for particular product
     *
     * @return array
     */
    public function getModuleAttributeForParticularProduct()
    {
        $registry = $this->registry->registry('product');
        if (isset($registry)) {
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
            $attribute['product_id'] = isset(
                $this->registry->registry('product')->getData()['entity_id']
            )?$this->registry->registry('product')->getData()['entity_id']:0;

            return $attribute;
        }
    }
}
