<?php
namespace BoostMyShop\OrderPreparation\Block\Packing\ChangeShippingMethodPopup;

class Calculator extends \BoostMyShop\OrderPreparation\Block\Packing\AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/ChangeShippingMethodPopup/Calculator.phtml';

    protected $_catalogProduct;
    protected $_carrierMethod = [];
    protected $_carriers = [];

    public function getInProgress()
    {
        return $this->_coreRegistry->registry('current_inprogress');
    }

    public function getTemplates()
    {
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();
        return $this->_templateCollectionFactory->create()->addActiveFilter()->addWarehouseFilter($warehouseId);
    }

    public function getDimension($dimension)
    {
        if ($this->getInProgress()->getData('ip_'.$dimension))
            return $this->getInProgress()->getData('ip_'.$dimension);
        else
        {
            $path = 'orderpreparation/packing/default_dimension_'.$dimension;
            $websiteId = $this->getInProgress()->getOrder()->getStore()->getwebsite_id();
            return $this->_config->getParamValue($path, $websiteId);
        }
    }

}