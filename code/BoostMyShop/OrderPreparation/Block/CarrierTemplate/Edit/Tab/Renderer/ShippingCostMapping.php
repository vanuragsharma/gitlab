<?php
namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Renderer;

class ShippingCostMapping extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    protected $_template = 'BoostMyShop_OrderPreparation::OrderPreparation/CarrierTemplate/Edit/Tab/Renderer/ShippingCostMapping.phtml';

    protected $_scopeConfig;
    protected $_shipConfig;
    protected $_carrierList;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shipConfig,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\ShippingMethod $carrierList,
        array $data = [])
    {
        $this->_shipConfig = $shipConfig;
        $this->_scopeConfig = $scopeConfig;
        $this->_carrierList  = $carrierList;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_element = $element;
        $html = $this->toHtml();
        return $html;
    }

    public function getShippingCostMappingData(){
        $model = $this->getModel();
        $mapping  = unserialize($model->getct_cost_matrix());
        return $mapping;
    }

    public function getCtShippingMethods(){
        $model = $this->getModel();
        $data  = unserialize($model->getct_shipping_methods());
        return $data;
    }

    public function getShippingMethodList(){
        $activeCarriers = $this->_shipConfig->getActiveCarriers();
        foreach($activeCarriers as $carrierCode => $carrierModel)
        {
            $options = array();
            if( $carrierMethods = $carrierModel->getAllowedMethods() )
            {
                $carrierTitle = $this->_scopeConfig->getValue('carriers/'.$carrierCode.'/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                foreach ($carrierMethods as $methodCode => $method)
                {
                    $code= $carrierCode.'_'.$methodCode;
                    $options[]=array('value'=>$code,'label'=>$method);
                    $methods[$code]= $carrierTitle.' - '.$method . ' ('.$carrierCode . '_' . $methodCode.')';
                }
            }
        }
        asort($methods);
        return $methods;
    }

    public function getShippingMethodOptionHtml($selectedCode=null){
        $methods = $this->getShippingMethodList();
        $ctShippingMethods = $this->getCtShippingMethods();
        $optionHtml = '<option value="all"  >All</option>';
        if($ctShippingMethods) {
            foreach($methods as $code=>$title){
                if(in_array($code, $ctShippingMethods)) {
                    if($selectedCode==$code) {
                        $selected='selected=selected';
                        $optionHtml .= '<option '.$selected.' value="'.$code.'">'.$title.'</option>';
                    }else
                        $optionHtml .= '<option  value="'.$code.'">'.$title.'</option>';
                }
            }
        }
        return $optionHtml;
    }
}
