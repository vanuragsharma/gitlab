<?php
namespace BoostMyShop\OrderPreparation\Block\Packing\ChangeShippingMethodPopup;

class Rates extends \BoostMyShop\OrderPreparation\Block\Packing\AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/ChangeShippingMethodPopup/Rates.phtml';

    public function getInProgress()
    {
        return $this->_coreRegistry->registry('current_inprogress');
    }

    public function getChangeShippingMethodUrl($methodCode)
    {
        return $this->getUrl('*/*/changeShippingMethod', ['method' => $methodCode, 'id' => $this->getInProgress()->getId()]);
    }

}