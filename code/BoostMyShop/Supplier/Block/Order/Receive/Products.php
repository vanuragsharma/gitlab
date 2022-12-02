<?php

namespace BoostMyShop\Supplier\Block\Order\Receive;

class Products extends \Magento\Backend\Block\Template
{
    protected $_template = 'Order/Receive/Products.phtml';

    protected $_coreRegistry = null;
    protected $_imageHelper;
    protected $_productCollectionFactory;
    protected $_orderProductCollectionFactory;
    protected $_config;
    protected $_product;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $orderProductCollectionFactory,
        \BoostMyShop\Supplier\Model\Config $config,
        \BoostMyShop\Supplier\Model\Product $product,
        array $data = []
    ){
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
        $this->_imageHelper = $imageHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_orderProductCollectionFactory = $orderProductCollectionFactory;
        $this->_config = $config;
        $this->_product = $product;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }

    public function getImageUrl($item)
    {
        $imageHelper = $this->_imageHelper->init($item->getProduct(), 'product_listing_thumbnail')->keepAspectRatio(true)->resize('400', '400');
        $imageUrl = $imageHelper->getUrl();
        return $imageUrl;
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/submitReception', ['po_id' => $this->_coreRegistry->registry('current_purchase_order')->getId()]);
    }

    public function getProductIdsJson()
    {
        return json_encode($this->_orderProductCollectionFactory->create()->getAlreadyAddedProductIds($this->getOrder()->getId()));
    }

    public function getProductUrl($item)
    {
        if ($this->_config->isErpIsInstalled())
            $url = $this->getUrl('erp/products/edit', ['id' => $item->getpop_product_id()]);
        else
            $url = $this->getUrl('catalog/product/edit', ['id' => $item->getpop_product_id()]);
        return $url;
    }

    public function getProductLocation($item)
    {
        return $this->_product->getLocation($item->getpop_product_id(), $this->getOrder()->getpo_warehouse_id());
    }

    public function getProductStockQuantity($productId)
    {
        return $this->_product->getStockQuantity($productId, $this->getOrder()->getpo_warehouse_id());
    }

    public function getProductBarcode($item)
    {
        return $this->_product->getBarcode($item->getpop_product_id());
    }

    public function getBarcodesJson()
    {
        $barcodes = array();
        $barcodeAttribute = $this->_config->getBarcodeAttribute();

        if ($barcodeAttribute)
        {
            $productIds = $this->_orderProductCollectionFactory->create()->getAlreadyAddedProductIds($this->getOrder()->getId());
            $collection = $this->_productCollectionFactory->create()->addAttributeToSelect($barcodeAttribute)->addFieldToFilter('entity_id', array('in' => $productIds));
            foreach($collection as $item)
            {
                if ($item->getData($barcodeAttribute))
                {
                    $barcodes[$item->getData($barcodeAttribute)] = $item->getId();
                }
                $barcodes[$this->removePrefix($item->getSku())] = $item->getId();
            }
        }

        return json_encode($barcodes);
    }

    public function isPackQtyEnabled()
    {
        return $this->_config->getSetting('general/pack_quantity');
    }

    public function getAdditionnalHtml()
    {
        $obj = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('bms_supplier_reception_after_products_block', ['order' => $this->getOrder(), 'obj' => $obj]);
        return $obj->getHtml();
    }

    public function getAdditionalColumnHtml($dataType, $item = null)
    {
        $obj = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('bms_supplier_order_reception_product_column', ['data_type' => $dataType, 'order' => $this->getOrder(), 'obj' => $obj, 'item' => $item]);
        return $obj->getHtml();
    }

    public function removePrefix($sku)
    {
        $t = explode('_', $sku);
        if (count($t) > 1)
            unset($t[0]);
        return implode('_', $t);
    }

}