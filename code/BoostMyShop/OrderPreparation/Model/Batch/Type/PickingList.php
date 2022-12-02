<?php namespace BoostMyShop\OrderPreparation\Model\Batch\Type;

class PickingList extends \BoostMyShop\OrderPreparation\Model\Pdf\PickingList
{
    public function insertGlobalData($pdf, \Zend_Pdf_Page $page, $batch)
    {
        $this->y = 552;
        $this->_setPdf($pdf);

        //assign bins to orders
        $binId = 1;
        foreach ($batch->getBatchOrders() as $orderInProgress) {
            $orderInProgress->setBinId(sprintf('%02d', $binId++));
        }

        $this->addSummaryPage($batch->getBatchOrders());


        foreach ($batch->getBatchOrders() as $orderInProgress) {
            $page = $this->newPage();
            $this->y -= 52;

            $this->insertLogo($page, $orderInProgress->getStore());
            $this->insertBarcode($page, $orderInProgress);

            $this->drawOrderInformation($page, $orderInProgress);
            $this->drawAddresses($page, $orderInProgress->getOrder());
            $this->y -= 40;
            foreach ($orderInProgress->getAllItems() as $item){

                //hydrate record
                $item->setBarcode($this->_product->create()->getBarcode($item->getproduct_id()));
                $item->setLocation($this->_product->create()->getLocation($item->getproduct_id(), $this->_preparationRegistry->getCurrentWarehouseId()));

                $this->_drawProduct($item, $page, false);
                if ($this->y < 50) {
                    $page = $this->newPage();
                }
            }
        }
    }

    protected function insertBarcode($page, $orderInProgress)
    {
        //add barcode
        $barcodeImage = $this->_barcode->getZendPdfBarcodeImage($orderInProgress->getOrder()->geterpcloud_integration_id().'_'.$orderInProgress->getOrder()->getIncrementId());
        $x = 420;
        $y = 820;
        $width = 160;
        $height = 50;
        $page->drawImage($barcodeImage, $x, $y - $height, $x + $width, $y);

        //add bin
        if ($this->_config->getPickingPerBins())
        {
            $this->_setFontBold($page, 36);
            $page->drawCircle($x - 23, $y - $height + 30, 20, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
            $page->drawText($orderInProgress->getBinId(), $x - 40,  $y - $height + 20, 'UTF-8');
        }

        return $this;
    }

}
