<?php namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer;

class UpsOffline extends RendererAbstract
{
    public function getShippingLabelFile($orderInProgress, $carrierTemplate)
    {

        foreach($orderInProgress as $orderInProgress) {
            $shipment = $orderInProgress->getShipment();
            if($orderInProgress->getip_total_weight() <= 0)
                throw new \Exception(__('weight must be > 0'));

            $data = $this->getLabelPdf($shipment, $orderInProgress->getip_total_weight(),$orderInProgress);

            if (isset($data['tracking_number'])) {
                if (is_array($data['tracking_number']))
                {
                    foreach($data['tracking_number'] as $trackingNumber)
                        $this->attachTrackingToShipment($shipment, $trackingNumber);
                }
                else
                    $this->attachTrackingToShipment($shipment, $data['tracking_number']);
            }

            if (!isset($data['pdf']))
                throw new \Exception(__('Ups label not available.'));

            $labelPdf = $data['pdf'];
            if (!$labelPdf) {
                throw new \Exception(__('Ups label not available'));
            }

            return $labelPdf;
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

            if (isset($data['pdf']))
                $shippingLabelData['file'] = $data['pdf'];

            if (isset($data['shipping_cost']))
                $shippingLabelData['shipping_cost'] = $data['shipping_cost'];

            return $shippingLabelData;
        }

        return $shippingLabelData;
    }

    public function getLabelPdf($shipment, $weight, $orderInProgress = null)
    {
        $upsOfflineLabel = $this->getObjectManager()->create('BoostMyShop\UpsLabel\Helper\Config');
        $upsOfflineLabel->setPackages($orderInProgress->getip_boxes());

        if ($orderInProgress && $orderInProgress->getCarrierTemplate())
            $upsOfflineLabel->setCarrierTemplate($orderInProgress->getCarrierTemplate());

        return $upsOfflineLabel->getShippingLabelByShipment($shipment, $weight);
    }

    protected function attachTrackingToShipment($shipment, $trackingNumber)
    {
        if ($shipment->getId())
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

    public function supportMultiboxes(){
        return true;
    }

    public function addCustomTabToCarrierTemplate($carrierTemplate, $tabs, $layout)
    {
        $tabs->addTab(
            'ups_section',
            [
                'label' => __('UPS settings'),
                'title' => __('UPS settings'),
                'content' => $layout->createBlock('BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Renderer\Ups')->toHtml()
            ]
        );
    }

    public function checkConnection($carrierTemplate)
    {
        $upsOfflineLabel = $this->getObjectManager()->create('BoostMyShop\UpsLabel\Helper\Config');
        $upsOfflineLabel->setCarrierTemplate($carrierTemplate);
        return $upsOfflineLabel->checkConnection();
    }

    public function canGetRates()
    {
        return true;
    }

    public function getRates($carrierTemplate, $orderInProgress)
    {
        $obj = $this->getObjectManager()->create('BoostMyShop\UpsLabel\Helper\Config');
        $obj->setCarrierTemplate($carrierTemplate);
        $upsRates = $obj->getRatesFromInProgress($orderInProgress);

        $rates = [];

        foreach($upsRates as $upsRate)
        {
            $mappedMethod = '';
            $label = $upsRate->Service->Description;
            if (!$label)
            {
                switch($upsRate->Service->Code)
                {
                    case '01':
                        $label = 'Next Day Air';
                        $mappedMethod = 'upsoffline_nextdayair';
                        break;
                    case '02':
                        $label = 'Second Day Air';
                        $mappedMethod = 'upsoffline_2nddayair';
                        break;
                    case '03':
                        $label = 'Ground';
                        $mappedMethod = 'upsoffline_ground';
                        break;
                    case '12':
                        $label = 'Three-Day Select';
                        $mappedMethod = 'upsoffline_threedayselect';
                        break;
                    case '13':
                        $label = 'Next Day Air Saver';
                        break;
                    case '14':
                        $label = 'Next Day Air Early A.M.';
                        break;
                    case '59':
                        $label = 'Second Day Air AM';
                        break;
                }
            }

            $monetaryValue = $upsRate->TotalCharges->MonetaryValue;
            if (isset($upsRate->NegotiatedRateCharges->TotalCharge->MonetaryValue))
            {
                if ($upsRate->NegotiatedRateCharges->TotalCharge->MonetaryValue < $monetaryValue)
                    $monetaryValue = $upsRate->NegotiatedRateCharges->TotalCharge->MonetaryValue;
            }

            $rates[] = ['label' => $label, 'method' => $upsRate->Service->Code, 'price' => $monetaryValue, 'etd' => '', 'mapped_method' => $mappedMethod];
        }


        return $rates;
    }

}
