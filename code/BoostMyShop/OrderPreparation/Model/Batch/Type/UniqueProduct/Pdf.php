<?php
namespace BoostMyShop\OrderPreparation\Model\Batch\Type\UniqueProduct;

class Pdf extends \BoostMyShop\OrderPreparation\Model\Batch\Type\AbstactPdf
{
    protected function insertTypeData(&$page, $batch)
    {
        if ($this->y < 25) {$page = $this->newPage();}

        $skuName = "";
        $item = $batch->getBatchOrderItems()->getFirstItem();
        if($item->getId()) {
            $product = $item->getOrderItem()->getProduct();
            $skuName = $this->cleanReference($product->getSku()). " + ". $product->getName();
        }
        $text = __('Product')." : ".$skuName;
        foreach ($this->string->split($text, 57, true, true) as $_value) {
            $page->drawText(trim(strip_tags($_value)), 20, $this->y,  'UTF-8');
            $this->y -= 15;
            if ($this->y < 25) {$page = $this->newPage();}
        }

        $qty = 0;
        foreach ($batch->getBatchOrderItems() as $item){
            $qty += $item->getipi_qty();
        }
        $text = __('Quantity')." : ".$qty;
        $page->drawText(trim(strip_tags($text)),20 , $this->y, 'UTF-8');
        $this->y -= 15;
        if ($this->y < 25) {$page = $this->newPage();}

        $location = $this->_opProduct->getLocation($item->getOrderItem()->getProduct()->getId(), $batch->getbob_warehouse_id());
        $text = __('Location')." : ".$location;
        foreach ($this->string->split($text, 65, true, true) as $_value) {
            $page->drawText(trim(strip_tags($_value)), 20, $this->y,  'UTF-8');
            $this->y -= 15;
            if ($this->y < 25) {$page = $this->newPage();}
        }

        $this->addOrderLabelPdf($batch);
    }

    public function addOrderLabelPdf($batch)
    {
        foreach($batch->getBatchOrders() as $ipOrder) {
            if($ipOrder->getip_shipping_label_pregenerated_label_path())
            {
                $documentContent = @file_get_contents($ipOrder->getip_shipping_label_pregenerated_label_path());
                $this->_pdfs[] = $documentContent;
            }
            else
            {
                if($ipOrder->getip_shipping_label_pregenerated_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPING_LABEL_PREGENERATED_ERROR) {
                    $this->getErrorOrderPage($ipOrder->getOrder());
                }
            }
        }
    }
}