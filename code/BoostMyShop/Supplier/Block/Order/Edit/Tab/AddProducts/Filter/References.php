<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Filter;

class References extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    protected $_supplierProductFactory;
    protected $_productFactory = null;
    protected $_config = null;
    protected $_coreRegistry = null;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Framework\DB\Helper $resourceHelper,
                                \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Product\CollectionFactory $supplierProductFactory,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
                                \BoostMyShop\Supplier\Model\Config $config,
                                \Magento\Framework\Registry $coreRegistry,
                                array $data = [])
    {
        parent::__construct($context, $resourceHelper, $data);

        $this->_supplierProductFactory = $supplierProductFactory;
        $this->_productFactory = $productFactory;
        $this->_config = $config;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Get condition
     *
     * @return array|null
     */
    public function getCondition()
    {
        if ($this->getValue() === null) {
            return null;
        }

        //Supplier SKU
        $supplierId = $this->getOrder()->getpo_sup_id();
        $productIds = $this->_supplierProductFactory->create()->getProductIdsForSupplierSku($supplierId, $this->getValue());

        //SKU
        $SkuProductIds = array();
        $productCollection = $this->_productFactory->create()->addFieldToFilter('sku', array('like' => '%'.$this->getValue().'%'));
        foreach($productCollection as $product){
            array_push($productIds, $product->getId());
        }

        //MPN
        if($this->_config->getMpnAttribute()) {
            $productCollection2 = $this->_productFactory->create()
                ->addAttributeToSelect($this->_config->getMpnAttribute())
                ->addAttributeToFilter($this->_config->getMpnAttribute(), array('like' => '%'.$this->getValue().'%'));
            foreach($productCollection2 as $product){
                array_push($productIds, $product->getId());
            }
        }

        //Barcode
        if($this->_config->getBarcodeAttribute()) {
            $productCollection3 = $this->_productFactory->create()
                ->addAttributeToSelect($this->_config->getBarcodeAttribute())
                ->addAttributeToFilter($this->_config->getBarcodeAttribute(), array('like' => '%'.$this->getValue().'%'));
            foreach($productCollection3 as $product){
                array_push($productIds, $product->getId());
            }	
        }

        return ['in' => $productIds];
    }

    protected function getOrder()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }
}
