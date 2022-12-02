<?php
namespace BoostMyShop\OrderPreparation\Block\Packing\ChangeShippingMethodPopup;

class AllMethods extends \BoostMyShop\OrderPreparation\Block\Packing\AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/ChangeShippingMethodPopup/AllMethods.phtml';

    protected $_catalogProduct;
    protected $_carrierMethod = [];
    protected $_carriers = [];

    public function getInProgress()
    {
        return $this->_coreRegistry->registry('current_inprogress');
    }


    public function getCarriers()
    {
        return $this->_carrierHelper->getCarriers();
    }

    public function getCurrentMethod()
    {
        return $this->getInProgress()->getOrder()->getshipping_method();
    }

    public function getChangeShippingMethodUrl($methodCode)
    {
        return $this->getUrl('*/*/changeShippingMethod', ['method' => $methodCode, 'id' => $this->getInProgress()->getId()]);
    }

    public function getMethods($carrier)
    {
        return $this->_carrierHelper->getMethods($carrier);
    }

    public function init()
    {
        if(empty($this->_carrierMethod) && empty($this->_carriers)) {
            foreach($this->_templateCollectionFactory->create() as $carrierTemplate)
            {
                foreach (unserialize($carrierTemplate->getct_shipping_methods()) as $shippingMethod) {
                    $carrier = explode('_',$shippingMethod);
                    if(!in_array($carrier[0],$this->_carriers))
                        $this->_carriers[] = $carrier[0];
                    if(!in_array($shippingMethod, $this->_carrierMethod))
                        $this->_carrierMethod[] = $shippingMethod;
                }
            }
        }
    }

    public function getCarrierCodeInCarrierTemplate()
    {
        $this->init();
        return $this->_carriers;
    }

    public function getmethodsInCarrierTemplate()
    {
        $this->init();
        return $this->_carrierMethod;
    }
}