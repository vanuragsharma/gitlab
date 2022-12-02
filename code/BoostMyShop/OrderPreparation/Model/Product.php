<?php

namespace BoostMyShop\OrderPreparation\Model;

class Product
{
    protected $_configFactory = null;
    protected $_productHelper = null;
    protected $_stockState = null;
    protected $_dir = null;
    protected $_configurableHelper = null;
    protected $_productFactory = null;
    protected $_product = null;

    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\BoostMyShop\OrderPreparation\Model\ConfigFactory $configFactory,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Magento\Catalog\Model\Product $product,
                                \Magento\Framework\App\Filesystem\DirectoryList $dir,
                                \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $configurableHelper,
                                \Magento\Catalog\Helper\Product $productHelper,
                                \Magento\CatalogInventory\Api\StockStateInterface $stockState

){
        $this->_configFactory = $configFactory;
        $this->_productFactory = $productFactory;
        $this->_product = $product;
        $this->_productHelper = $productHelper;
        $this->_stockState = $stockState;
        $this->_dir = $dir;
        $this->_configurableHelper = $configurableHelper;
    }

    public function getLocation($productId, $warehouseId)
    {
        $attributeCode = $this->_configFactory->create()->getLocationAttribute();
        if ($attributeCode)
        {
            $product = $this->_productFactory->create()->load($productId);
            return $product->getData($attributeCode);
        }
        return "";
    }

    public function setLocation($productId)
    {

    }

    public function getMagentoQty($productId, $websiteId)
    {
        return $this->_stockState->getStockQty($productId, $websiteId);
    }

    public function getImageUrl($productId)
    {
        $url = '';
        $product = $this->_productFactory->create()->load($productId);
        if ($product->getThumbnail())
            $url = $this->_productHelper->getThumbnailUrl($product);

        //check with parent if no url
        if (!$url)
        {
            $parentIds = $this->_configurableHelper->create()->getParentIdsByChild($productId);
            if (isset($parentIds[0])) {
                $parentProduct = $this->_productFactory->create()->load($parentIds[0]);
                $url = $this->_productHelper->getThumbnailUrl($parentProduct);
            }
        }

        return $url;
    }

    public function getImagePath($productId)
    {
        $fullPath = '';
        $product = $this->_productFactory->create()->load($productId);
        if ($product->getData('thumbnail'))
            $fullPath = '/'.'catalog'.'/'.'product'.$product->getData('thumbnail');
        else
        {
            $parentIds = $this->_configurableHelper->create()->getParentIdsByChild($productId);
            if (isset($parentIds[0])) {
                $parentProduct = $this->_productFactory->create()->load($parentIds[0]);
                if ($parentProduct->getData('thumbnail'))
                    $fullPath = '/'.'catalog'.'/'.'product'.$parentProduct->getData('thumbnail');
            }
        }

        return $fullPath;
    }

    public function getBarcode($productId)
    {
        $attributeCode = $this->_configFactory->create()->getBarcodeAttribute();
        if ($attributeCode)
        {
            return $this->getAttributeValue($productId, $attributeCode);
        }
        return "";
    }
    public function getAdditionalBarcodes($productId)
    {
        $barcodeArray = [];
        return json_encode($barcodeArray);
    }

    public function getMpn($productId)
    {
        $attributeCode = $this->_configFactory->create()->getMpnAttribute();
        if ($attributeCode)
        {
            $product = $this->_productFactory->create()->load($productId);
            return $product->getData($attributeCode);
        }
        return "";
    }
    public function getAttributeValue($productId, $attributeCode)
    {
        $value =  $this->_product->getResource()->getAttributeRawValue(
                    $productId,
                    $attributeCode,
                    0
                );
        if (!is_array($value))
            return $value;
    }

}

