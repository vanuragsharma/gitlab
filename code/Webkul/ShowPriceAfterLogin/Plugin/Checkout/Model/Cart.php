<?php
namespace Webkul\ShowPriceAfterLogin\Plugin\Checkout\Model;

use Magento\Framework\Exception\LocalizedException;

class Cart
{
     /**
      * Plugin constructor.
      * @param \Webkul\ShowPriceAfterLogin\Helper\Data $helper
      * @param \Magento\Catalog\Model\ProductFactory $productModel
      */
    public function __construct(
        \Webkul\ShowPriceAfterLogin\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productModel
    ) {
                $this->helper = $helper;
        $this->productModel = $productModel;
    }

    /**
     * beforeAddProduct
     *
     * @param      $subject
     * @param      $productInfo
     * @param null $requestInfo
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        $enableOrDisableStoreField=$this->helper->storeAvilability();
        $isAllowedCustomerGroups = $this->helper->isAllowedCustomerGroups();
        if ($enableOrDisableStoreField) {
            if (! $isAllowedCustomerGroups) {
                $productId = $productInfo->getId();
                $productModel = $this->productModel->create()->load($productId);
                $productCategories=$productInfo->getCategoryIds();
                $productAllowedCategories=$this->helper->getListOfCategories();
                $enableOrDisableCategory=$this->helper->statusOfCategorySettingForAllUser();
                $result = array_intersect(explode(',', $productAllowedCategories), $productCategories);
                if ($productModel->getData('show_price') &&
                 $this->helper->configPriority() == "product_configuration") {
                    return [$productInfo, $requestInfo];
                }
                if (!$enableOrDisableCategory || empty($result)) {
                    throw new LocalizedException(__('Logged in user not allowed to view price'));
                }
                return [$productInfo, $requestInfo];
            }
        }
        return [$productInfo, $requestInfo];
    }
}
