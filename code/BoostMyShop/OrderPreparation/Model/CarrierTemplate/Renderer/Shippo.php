<?php namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer;

class Shippo extends RendererAbstract
{
    public function getShippingLabelFile($orderInProgress, $carrierTemplate)
    {
        foreach($orderInProgress as $orderInProgress) {
            $shipment = $orderInProgress->getShipment();
            $data = $this->getLabelPdf($shipment);
            $labelPdf = $data['pdf'];
            if(!$labelPdf){
                throw new \Exception(__('An error occurred during the generation of the Chronopost label'));
            }
            
            if(isset($data['tracking_number'])){
                $this->attachTrackingToShipment($shipment, $data['tracking_number']);
            }

            return $labelPdf;
        }
    }

    public function getShippingLabelData($ordersInProgress, $carrierTemplate)
    {
        $shippingLabelData = ['file' => false, 'trackings' => []];

        foreach($ordersInProgress as $orderInProgress) {
            $shipment = $orderInProgress->getShipment();
            $data = $this->getLabelPdf($shipment);

            if (isset($data['tracking_number'])) {
                if (is_array($data['tracking_number'])) {
                    $shippingLabelData['trackings'] = $data['tracking_number'];
                } else
                    $shippingLabelData['trackings'][] = $data['tracking_number'];
            }

            if (isset($data['pdf']))
                $shippingLabelData['file'] = $data['pdf'];

            return $shippingLabelData;
        }

        return $shippingLabelData;
    }

    public function getLabelPdf($shipment)
    {
        $shippoLabel = $this->getObjectManager()->create('BoostMyShop\Shippo\Model\ShippingLabel');
        return $shippoLabel->getShippingLabel($shipment);
    }

    protected function attachTrackingToShipment($shipment, $trackingNumber)
    {
        $track = $this->getObjectManager()->create('Magento\Sales\Model\Order\Shipment\Track');

        $shippingMethod = $shipment->getOrder()->getShippingMethod();
        $carrierCode = explode('_', $shippingMethod);

        $data = array(
            'carrier_code' => $carrierCode[0]."_".$carrierCode[2],
            'title' => $shipment->getOrder()->getShippingDescription(),
            'number' => $trackingNumber
        );
        $track->addData($data);

        $shipment->addTrack($track);
        $track->save();
    }
}
