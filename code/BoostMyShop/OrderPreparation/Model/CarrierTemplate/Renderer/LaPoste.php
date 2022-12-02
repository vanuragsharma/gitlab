<?php namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer;

class LaPoste extends RendererAbstract
{
    public function getShippingLabelFile($orderInProgress, $carrierTemplate)
    {
        foreach($orderInProgress as $orderInProgress) {
            $shipment = $orderInProgress->getShipment();
            if($orderInProgress->getip_total_weight() <= 0)
                throw new \Exception(__('weight must be > 0'));

            $data = $this->getLabelPdf($shipment, $orderInProgress->getip_total_weight());

            if (isset($data['tracking_number'])) {
                $this->attachTrackingToShipment($shipment, $data['tracking_number']);
            }

            if (!isset($data['pdf']))
                throw new \Exception(__('laPoste label not available.'));

            $labelPdf = $data['pdf'];
            if (!$labelPdf) {
                throw new \Exception(__('laPoste label not available'));
            }

            return $labelPdf;
        }
    }

    public function getShippingLabelData($ordersInProgress, $carrierTemplate)
    {
        $shippingLabelData = ['file' => false, 'trackings' => []];

        foreach($ordersInProgress as $orderInProgress) {
            $shipment = $orderInProgress->getShipment();
            if ($orderInProgress->getip_total_weight() <= 0)
                throw new \Exception(__('weight must be > 0'));

            $data = $this->getLabelPdf($shipment, $orderInProgress->getip_total_weight());

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

    public function getLabelPdf($shipment, $weight)
    {
        $laPosteLabel = $this->getObjectManager()->create('BoostMyShop\LaPoste\Helper\Config');
        return $laPosteLabel->getShippingLabelByShipment($shipment, $weight);
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
}
