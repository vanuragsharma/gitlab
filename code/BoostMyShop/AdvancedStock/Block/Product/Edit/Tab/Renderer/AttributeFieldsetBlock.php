<?php
namespace BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\Renderer;

class AttributeFieldsetBlock extends \Magento\Backend\Block\Template
{
    protected $_coreRegistry;
    protected $_barcodeFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\AdvancedStock\Model\BarcodesFactory $barcodeFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = [])
    {
        $this->_coreRegistry = $coreRegistry;
        $this->_barcodeFactory = $barcodeFactory;

        parent::__construct($context, $data);
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getProductOptions()
    {
        $collection=array();
        $productId = $this->getProduct()->getId();

        if($productId){
            $collection = $this->_barcodeFactory->create()->getCollection()->addProductFilter($productId);
        }
        return  $collection;
    }

}
