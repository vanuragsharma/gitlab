<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_ShowPriceAfterLogin
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\ShowPriceAfterLogin\Block\Adminhtml;

class CustomAttribute extends \Magento\Backend\Block\Template
{

    /**
     * @var string
     */
    protected $_template = 'customattribute.phtml';

    /**
     * @var \Webkul\ShowPriceAfterLogin\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
 
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\ShowPriceAfterLogin\Helper\Data $dataHelper
     * @param \Magento\Catalog\Model\ProductFactory   $productFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\ShowPriceAfterLogin\Helper\Data $dataHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
    
        $this->dataHelper = $dataHelper;
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * get all list of group attribute of customer to show on particular product page
     *
     * @return Array
     */
    public function getGroupsLists()
    {
        return $this->dataHelper->getGroupsLists();
    }

    /**
     * return all the attribute of product related to show price.
     *
     * @return Array
     */
    public function getAllShowPriceAfterLoginModuleAttribute()
    {
        $attribute = ['show_price'=>"",'call_for_price'=>"",'show_price_customer_group'=>"",
                      'call_for_price_label'=>"",'call_for_price_link'=>""];
        $post  = $this->getRequest()->getParams();
        if (isset($post['id'])) {
            $productId = $post['id'];
            $product = $this->productFactory->create()->load($productId);
            $attribute['show_price'] = $product->getShowPrice();
            $attribute['call_for_price'] = $product->getCallForPrice();
            $attribute['show_price_customer_group'] = $product->getShowPriceCustomerGroup();
            $attribute['call_for_price_label'] = $product->getCallForPriceLabel();
            $attribute['call_for_price_link'] = $product->getCallForPriceLink();
        }
        return $attribute;
    }

    /**
     * getProductId function grt the product id
     *
     * @return int
     */
    public function getProductId()
    {
        $id = $this->getRequest()->getParam('id');
        if (isset($id)) {
            return $id;
        }
        return 0;
    }
}
