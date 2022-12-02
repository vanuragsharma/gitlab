<?php
namespace BoostMyShop\OrderPreparation\Block;

class HoldOrderPopup extends \BoostMyShop\OrderPreparation\Block\Packing\AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/HoldOrderPopup.phtml';

    public function getHoldOrderUrl()
    {
        return $this->getUrl('*/*/holdOrder', ['order_id' => $this->currentOrderInProgress()->getip_id()]);
    }
}
