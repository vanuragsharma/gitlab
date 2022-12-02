<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class Footer extends AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/Footer.phtml';

    public function getHoldOrderUrl()
    {
        return $this->getUrl('*/*/holdOrderPopup', ['order_id' => $this->currentOrderInProgress()->getId()]);
    }
    public function getPackingUrl()
    {
        return $this->getUrl('*/*/index');
    }
    public function isLabelPregenerated()
    {
        $orderInProgress = $this->currentOrderInProgress();
        return $orderInProgress->getip_shipping_label_pregenerated_label_path()?true:false;
    }

    public function getDeletePregeneratedLabelUrl()
    {
        return $this->getUrl('*/*/deletePregeneratedLabel', ['order_id' => $this->currentOrderInProgress()->getId()]);
    }
}

