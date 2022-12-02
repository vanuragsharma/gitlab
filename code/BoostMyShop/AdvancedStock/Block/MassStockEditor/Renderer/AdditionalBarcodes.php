<?php

namespace BoostMyShop\AdvancedStock\Block\MassStockEditor\Renderer;

use Magento\Framework\DataObject;

class AdditionalBarcodes extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_config;
    protected $_barcodeFactory;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\AdvancedStock\Model\Config $config,
                                \BoostMyShop\AdvancedStock\Model\BarcodesFactory $barcodeFactory,
                                array $data = [])
    {
        parent::__construct($context, $data);
        $this->_config = $config;
        $this->_barcodeFactory = $barcodeFactory;
    }

    public function render(DataObject $row)
    {
        $collection = $this->_barcodeFactory->create()->getCollection()->addProductFilter($row->getId());
        $html ='';
        $html ='<span>'.$row->getData($this->_config->getBarcodeAttribute()).'</span><br/>';
        if($collection->getSize()>0){
            foreach ($collection as $item){
                $html .= '<span>'.$item->getbac_code().'</span><br/>';
            }
        }
        return $html;
    }

    public function renderExport(DataObject $row)
    {
        return $row->getData($this->_config->getBarcodeAttribute());
    }

}