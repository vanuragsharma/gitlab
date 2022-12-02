<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class Confirm extends AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/Confirm.phtml';

    public function canDisplay()
    {
        return ($this->hasOrderSelect()
                &&
                (
                    $this->currentOrderInProgress()->getip_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED
                    ||
                    $this->currentOrderInProgress()->getip_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PACKED
                )
                );
    }

    public function getDownloadPackingSlipUrl()
    {
        if ($this->currentOrderInProgress()->getip_shipment_id())
        {
            return $this->getUrl('sales/shipment/print', ['shipment_id' => $this->currentOrderInProgress()->getip_shipment_id()]);
        }
    }

    public function getDownloadInvoiceUrl()
    {
        if ($this->currentOrderInProgress()->getip_invoice_id())
        {
            return $this->getUrl('sales/order_invoice/print', ['invoice_id' => $this->currentOrderInProgress()->getip_invoice_id()]);
        }
    }

    public function getDownloadPickingListUrl()
    {
        return $this->getUrl('*/*/download', ['document' => 'picking', 'order_id' => $this->currentOrderInProgress()->getId()]);
    }

    public function getDownloadShippingLabel()
    {
        $template = $this->_carrierTemplateHelper->getCarrierTemplateForOrder($this->currentOrderInProgress(), $this->_preparationRegistry->getCurrentWarehouseId());
        if ($template)
        {
            return $this->getUrl('*/*/download', ['document' => 'shipping_label', 'order_id' => $this->currentOrderInProgress()->getId()]);
        }
    }

    public function autoDownload()
    {
        return ($this->getRequest()->getParam('download') == 1);
    }

    public function getDownloadUrlsAsJson()
    {
        $urls = [];

        if ($this->_config->getSetting('packing/download_invoice'))
            $urls[] = $this->getDownloadInvoiceUrl();
        if ($this->_config->getSetting('packing/download_shipment'))
            $urls[] = $this->getDownloadPackingSlipUrl();
        if ($this->_config->getSetting('packing/download_shipping_label'))
            $urls[] = $this->getDownloadShippingLabel();

        return json_encode($urls);
    }

    public function getAdditionalButton()
    {
        $obj = new \Magento\Framework\DataObject();
        $obj->setHtmlButton("");
        $this->_eventManager->dispatch('bms_orderpreparation_packing_confirm_additional_button', ['in_progress' => $this->currentOrderInProgress(), 'obj' => $obj]);
        $htmlButton = $obj->getHtmlButton();

        return $htmlButton;
    }

}