<?php namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer;

class DpdCzech extends RendererAbstract
{
    public function getShippingLabelFile($orderInProgress, $carrierTemplate)
    {
        $pdfs = [];
        $raiseErrors = (count($orderInProgress) == 1);

        foreach($orderInProgress as $orderInProgress) {
            $labelPdf = false;
            $shipment = $orderInProgress->getShipment();

            try
            {
                $data = $this->getLabelPdf($shipment, $orderInProgress->getip_total_weight(), $orderInProgress);

                if (isset($data['tracking_number'])) {
                    $this->attachTrackingToShipment($shipment, $data['tracking_number']);
                }

                if (!isset($data['pdf']))
                    throw new \Exception(__('DpdCzech label not available.'));

                $labelPdf = $data['pdf'];
                if (!$labelPdf) {
                    throw new \Exception(__('DpdCzech label not available'));
                }

                $pdfs[] = $labelPdf;
            }
            catch(\Exception $ex)
            {
                if ($raiseErrors)
                    throw new \Exception($ex->getMessage());
            }
        }

        //render
        if (count($pdfs) == 1)
            return $pdfs[0];
        else
        {
            //merge labels in a single pdf document
            return $this->mergePdfs($pdfs);
        }
    }

    public function getShippingLabelData($ordersInProgress, $carrierTemplate)
    {
        $shippingLabelData = ['file' => false, 'trackings' => [], 'shipping_cost' => ''];

        foreach($ordersInProgress as $orderInProgress) {
            $shipment = $orderInProgress->getShipment();
            if ($orderInProgress->getip_total_weight() <= 0)
                throw new \Exception(__('weight must be > 0'));

            $data = $this->getLabelPdf($shipment, $orderInProgress->getip_total_weight(), $orderInProgress);

            if (isset($data['tracking_number'])) {
                if (is_array($data['tracking_number'])) {
                    $shippingLabelData['trackings'] = $data['tracking_number'];
                } else
                    $shippingLabelData['trackings'][] = $data['tracking_number'];
            }

            $labelPdf = $data['pdf'];
            if (!$labelPdf) {
                throw new \Exception(__('DpdCzech label not available'));
            }

            $pdfs[] = $labelPdf;
            if (count($pdfs) == 1)
                $shippingLabelData['file'] = $pdfs[0];
            else
                $shippingLabelData['file'] = $this->mergePdfs($pdfs);

            if (isset($data['shipping_cost']))
                $shippingLabelData['shipping_cost'] = $data['shipping_cost'];

            return $shippingLabelData;
        }

        return $shippingLabelData;
    }

    public function getLabelPdf($shipment, $weight, $orderInProgress = null)
    {
        $dpdLabel = $this->getObjectManager()->create('BoostMyShop\DpdCz\Helper\Config');
        $dpdLabel->setPackages($orderInProgress->getip_boxes());
        return $dpdLabel->getShippingLabelByShipment($shipment, $weight);
    }

    protected function attachTrackingToShipment($shipment, $trackingNumber)
    {
        $track = $this->getObjectManager()->create('Magento\Sales\Model\Order\Shipment\Track');

        $shippingMethod = $shipment->getOrder()->getShippingMethod();
        $carrierCode = explode('_', $shippingMethod);
        $data = array(
            'carrier_code' => isset($carrierCode[0])?$carrierCode[0]:$shippingMethod,
            'title' => $shipment->getOrder()->getShippingDescription(),
            'number' => $trackingNumber
        );
        $track->addData($data);
        $shipment->addTrack($track);
        $track->save();
    }

    protected function mergePdfs($pdfs)
    {
        $mergedPdf = new \Zend_Pdf();

        foreach($pdfs as $pdf)
        {
            $pdf1 = \Zend_Pdf::parse($pdf, 1);
            $template = clone $pdf1->pages[0];
            $page1 = new \Zend_Pdf_Page($template);
            $mergedPdf->pages[] = $page1;
        }

        return $mergedPdf->render();
    }

    public function supportMultiboxes(){
        return true;
    }
}
