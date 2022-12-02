<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class MondialRelay extends RendererAbstract
{
    public function getShippingLabelFile($ordersInProgress, $carrierTemplate){

        foreach($ordersInProgress as $orderInProgress)
        {
            $shipment = $orderInProgress->getShipment();

            $this->checkMondialRelayCredentials($shipment);

            if ($shipment) {
                $increment_id = $shipment->getincrement_id();
                $fileName = $increment_id . ".pdf";
                $path = $this->_directory->getPath("var") . "/mondialrelay/" . $increment_id;
                $filePath = rtrim($path, '/') . '/' . $fileName;

                if (!file_exists($filePath)) {

                    $response = $this->createLabel($orderInProgress);
                    $data = $response["info"][0];
                    $label = $data['label_content'];

                    if($label)
                        $this->createFile($filePath, $data['label_content'], DirectoryList::VAR_DIR);

                    if (isset($data['tracking_number'])) {
                        $this->attachTrackingToShipment($shipment, $data['tracking_number']);
                    }
                } else {
                    $label = @file_get_contents($filePath);
                }

                return $label;
            }
        }
    }

    public function getShippingLabelData($ordersInProgress, $carrierTemplate)
    {
        $shippingLabelData = ['file' => false, 'trackings' => []];

        foreach($ordersInProgress as $orderInProgress)
        {
            $shipment = $orderInProgress->getShipment();

            $this->checkMondialRelayCredentials($shipment);

            if ($shipment) {
                    $response = $this->createLabel($orderInProgress);
                    $data = $response["info"][0];

                if (isset($data['label_content']))
                    $shippingLabelData['file'] = $data['label_content'];

               if (isset($data['tracking_number'])) {
                   if (is_array($data['tracking_number']))
                       $shippingLabelData['trackings'] = $data['tracking_number'];
                   else
                       $shippingLabelData['trackings'][] = $data['tracking_number'];
                }
            }

            return $shippingLabelData;
        }

        return $shippingLabelData;
    }

    public function getLabelPdf($shipment, $weight, $orderInProgress = null)
    {
        $response = $this->createLabel($orderInProgress);
        if (!isset($response["info"][0]))
            throw new \Exception('Unable to retrieve mondial relay label');
        $response = $response["info"][0];

        $data = [];
        $data['pdf'] = $response['label_content'];
        $data['tracking_number'] = $response['tracking_number'];

        return $data;
    }

    public function checkMondialRelayCredentials ($shipment) {

        if(!$this->_config->getGlobalSetting('carriers/mondialrelay/active', $shipment->getStoreId()))
            throw new \Exception(__('Mondial Relay is not enabled in Settings > Shipping'));

        if(!$this->_config->getGlobalSetting('carriers/mondialrelay/api_company', $shipment->getStoreId()))
            throw new \Exception(__('Mondial Relay API Company is not defined in Settings > Shipping'));

        if(!$this->_config->getGlobalSetting('carriers/mondialrelay/api_reference', $shipment->getStoreId()))
            throw new \Exception(__('Mondial Relay API Reference is not defined in Settings > Shipping'));

        if(!$this->_config->getGlobalSetting('carriers/mondialrelay/api_key', $shipment->getStoreId()))
            throw new \Exception(__('Mondial Relay API Key is not defined in Settings > Shipping'));

        if(!$this->_config->getGlobalSetting('carriers/mondialrelay/label/shipper_name', $shipment->getStoreId()))
            throw new \Exception(__('Mondial Relay Commercial name is not defined in Settings > Shipping'));

    }

    public function createLabel($orderInProgress)
    {
        if($orderInProgress->getip_total_weight() <= 0)
            throw new \Exception(__('The weight must be higher than 0'));

        $shipment = $orderInProgress->getShipment();
        $order = $shipment->getOrder();
        $shippingMethod = $order->getShippingMethod();

        $shippingHelper = $this->getObjectManager()->create('MondialRelay\Shipping\Helper\Data');

        $address = $order->getShippingAddress();
        if (!$address)
            $address = $order->getBillingAddress();

        $streets = $address->getstreet();

        list($code, $method) = explode('_', $shippingMethod);

        if($code != 'mondialrelay')
            throw new \Exception(__('This order is not associated to a Mondial Relay shipping method'));

        if($method != 'pickup'){
            $code = $shippingHelper->getConfig($method . '/code');
        } else {
            switch(true){
                case (float) $orderInProgress->getip_total_weight() <= 30:
                    $code = '24R';
                    break;
                case (float) $orderInProgress->getip_total_weight() <= 50:
                    $code = '24L';
                    break;
                case (float) $orderInProgress->getip_total_weight() <= 150:
                    $code = 'DRI';
                    break;
                default:
                    $code = '24R';
                    break;
            }
        }

        $livRel = '';
        if($order->geterpcloud_relay_point() !== null)
            $livRel = $order->geterpcloud_relay_point();

        $nbCol = ($orderInProgress->getip_parcel_count() > 0) ? $orderInProgress->getip_parcel_count() : 1;

        $packages = array();
        for($i=1; $i<=$nbCol; $i++){
            array_push($packages, ['params' => array('weight_units' => 'kgs')]);
        }
        
        $recipientEmail = $order->getCustomerEmail() ? : '';

        $data = (
            [
                'order_shipment'                            => $shipment,
                'mode_liv'                                  => $code,
                'liv_rel'                                   => $livRel,
                'packages'                                  => $packages,
                'package_weight'                            => $orderInProgress->getip_total_weight(),
                'shipper_contact_person_name'               => 'admin admin',
                'shipper_contact_person_first_name'         => 'admin',
                'shipper_contact_person_last_name'          => 'admin',
                'shipper_contact_company_name'              => $this->_config->getGlobalSetting('carriers/mondialrelay/label/shipper_name', $shipment->getStoreId()),
                'shipper_contact_phone_number'              => $this->_config->getGlobalSetting('general/store_information/phone', $shipment->getStoreId()),
                'shipper_address_street'                    => $this->_config->getGlobalSetting('shipping/origin/street_line1', $shipment->getStoreId()),
                'shipper_address_street_1'                  => $this->_config->getGlobalSetting('shipping/origin/street_line1', $shipment->getStoreId()),
                'shipper_address_street_2'                  => $this->_config->getGlobalSetting('shipping/origin/street_line2', $shipment->getStoreId()),
                'shipper_address_city'                      => $this->_config->getGlobalSetting('shipping/origin/city', $shipment->getStoreId()),
                'shipper_address_state_or_province_code'    => substr($this->_config->getGlobalSetting('shipping/origin/postcode', $shipment->getStoreId()), 0, 2),
                'shipper_address_postal_code'               => $this->_config->getGlobalSetting('shipping/origin/postcode', $shipment->getStoreId()),
                'shipper_address_country_code'              => $this->_config->getGlobalSetting('shipping/origin/country_id', $shipment->getStoreId()),
                'recipient_contact_person_name'             => $address->getlastname().' '.$address->getfirstname(),
                'recipient_contact_person_first_name'       => $address->getfirstname(),
                'recipient_contact_person_last_name'        => $address->getlastname(),
                'recipient_contact_company_name'            => $address->getcompany(),
                'recipient_contact_phone_number'            => $address->gettelephone(),
                'recipient_address_street'                  => implode(' ', $streets),
                'recipient_address_street_1'                => $streets[0],
                'recipient_address_street_2'                => isset($streets[1]) ? $streets[1] : '',
                'recipient_address_city'                    => $address->getcity(),
                'recipient_address_state_or_province_code'  => $address->getRegionCode(),
                'recipient_address_region_code'             => $address->getRegionCode(),
                'recipient_address_postal_code'             => $address->getpostcode(),
                'recipient_address_country_code'            => $address->getcountry_id(),
                'recipient_email'                           => $recipientEmail
            ]
        );

        $request = $this->getObjectManager()->create('Magento\Shipping\Model\Shipment\Request');
        $request->setData($data);

        $labelModel = $this->getObjectManager()->create('MondialRelay\Shipping\Model\Label');
        $response = $labelModel->doShipmentRequest($request);

        if ($response->getData('errors'))
            throw new \Exception(__('An error occured during label generation : %1', $response->getData('errors')));

        return $response;
    }

    public function createFile($fileNameWithPath, $content, $baseDir = DirectoryList::ROOT)
    {
        $dir = $this->_filesystem->getDirectoryWrite($baseDir);
        if ($content !== null) {
            try {
                $dir->writeFile($fileNameWithPath, $content);
            }catch(Exception $e){
                throw new \Exception(__("Error while created file : %1", $e->getMessage()));
            }
        }
    }

    protected function attachTrackingToShipment($shipment, $trackingNumber)
    {
        $track = $this->getObjectManager()->create('Magento\Sales\Model\Order\Shipment\Track');

        $shippingMethod = $shipment->getOrder()->getShippingMethod();
        list($carrierCode, $method) = explode('_', $shippingMethod, 2);

        $data = array(
            'carrier_code' => $carrierCode,
            'title' => $shipment->getOrder()->getShippingDescription(),
            'number' => $trackingNumber
        );
        $track->addData($data);

        $shipment->addTrack($track);
        $track->save();
    }

    public function getTrackingUrl($trackingNumber)
    {
        return "http://www.mondialrelay.fr/ww2/public/mr_suivi.aspx?cab=".$trackingNumber;
    }
}
