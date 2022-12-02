<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class References extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer{

    protected $_config;
    protected $_coreRegistry = null;
    protected $_supplierProductFactory = null;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\Supplier\Model\Supplier\ProductFactory $supplierProductFactory,
                                \Magento\Framework\Registry $coreRegistry,
                                \BoostMyShop\Supplier\Model\Config $config,
                                array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_supplierProductFactory = $supplierProductFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;
    }

    public function render(DataObject $row)
    {
        $html = '<table border="0" class="add_products_barcode_sku_supplier_sku">';;


        //SKU
        if ($this->_config->isErpIsInstalled())
            $url = $this->getUrl('erp/products/edit', ['id' => $row->getentity_id()]);
        else
            $url = $this->getUrl('catalog/product/edit', ['id' => $row->getentity_id()]);

        $html .= '<a href="'.$url.'" tabindex="-1">'.$this->getProductSku($row).'</a>';
        $html .= '<br/>';

        //Barcode
        if ($this->_config->getBarcodeAttribute()){
            $barcodeAttribute = $this->_config->getBarcodeAttribute();
            if($row->getData($barcodeAttribute)){
                $html .= $row->getData($barcodeAttribute);
                $html .= '<br/>';
            }
        }

        //MPN
        if ($this->_config->getMpnAttribute()){
            $mpnAttribute = $this->_config->getMpnAttribute();
            if($row->getData($mpnAttribute)){
                $html .= 'MPN: '.$row->getData($mpnAttribute);
                $html .= '<br/>';
            }
        }

        //Supplier SKU
        $productId = $row->getId();
        $supplierId = $this->getOrder()->getpo_sup_id();
        $productSupplier = $this->_supplierProductFactory->create()->loadByProductSupplier($productId, $supplierId);

        $html .= $productSupplier->getsp_sku();

        $html .= '</table>';

        return $html;
    }

    protected function getProductSku($row)
    {
        return $row->getsku();
    }

    protected function getOrder()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }

}