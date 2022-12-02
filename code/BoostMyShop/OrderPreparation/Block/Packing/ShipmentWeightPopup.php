<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class ShipmentWeightPopup extends AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/ShipmentWeightPopup.phtml';

    public function getUpdateShipmentWeightUrl()
    {
        return $this->getUrl('*/*/updateShipmentWeight', ['order_id' => $this->currentOrderInProgress()->getId()]);
    }
}