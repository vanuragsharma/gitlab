<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class Boxes extends AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/Boxes.phtml';

    public function isMutliboxAllow(){
        $template = $this->_carrierTemplateHelper->getCarrierTemplateForOrder($this->currentOrderInProgress(), $this->_preparationRegistry->getCurrentWarehouseId());
        if($template){
            $render = $template->getRenderer();
            if($render)
                return $render->supportMultiboxes();
        }
        return false;
    }

    public function getBoxes()
    {
        $result = null;
        $boxes = json_decode($this->currentOrderInProgress()->getip_boxes(), true);
        if(is_array($boxes) && count($boxes)>0) {
            unset($boxes[1]);
            $result = $boxes;
        }
        return $result;
    }

    public function getCurrentOrderParameters()
    {
        if($this->isMutliboxAllow()) {
            $boxes = json_decode($this->currentOrderInProgress()->getip_boxes(), true);
            if(is_array($boxes) && count($boxes)>0)
                return $boxes[1];
        }
        return ['total_weight' =>  $this->currentOrderInProgress()->getip_total_weight() ? $this->currentOrderInProgress()->getip_total_weight() : $this->currentOrderInProgress()->getEstimatedWeight(),
            'parcel_count' =>   $this->currentOrderInProgress()->getip_parcel_count() ? $this->currentOrderInProgress()->getip_parcel_count() : 1,
            'parcel_length' =>  $this->currentOrderInProgress()->getip_length() ? $this->currentOrderInProgress()->getip_length() : $this->getDefaultDimension("length"),
            'parcel_width' =>   $this->currentOrderInProgress()->getip_width() ? $this->currentOrderInProgress()->getip_width() : $this->getDefaultDimension("width"),
            'parcel_height' =>  $this->currentOrderInProgress()->getip_height() ? $this->currentOrderInProgress()->getip_height() : $this->getDefaultDimension("height")
        ];

    }

    public function getDefaultDimension($dimension)
    {
        $path = 'orderpreparation/packing/default_dimension_'.$dimension;
        $websiteId = $this->currentOrderInProgress()->getOrder()->getStore()->getwebsite_id();
        return $this->_config->getParamValue($path, $websiteId);
    }

}