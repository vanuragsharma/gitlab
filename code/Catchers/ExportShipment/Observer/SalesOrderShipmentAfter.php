<?php

namespace Catchers\ExportShipment\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderShipmentAfter implements ObserverInterface
{
	public function __construct(
		\Magento\Catalog\Model\Product $product,
		\Magento\Framework\Filesystem $fileSystem,
		\Magento\Framework\App\RequestInterface $request

	) {        
		$this->product = $product;    
		$this->fileSystem = $fileSystem;  
		$this->_request = $request;  
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$shipmentData = $this->_request->getPost('shipment');
		if (isset($shipmentData['shipping_slips'])) {
			$username = 'i-catchers@oort.nl';
			$password = 'MJxu9Uqp';
			$host = 'ftp.oort.nl';
			$data = [];
			$shipmentItem = [];
			$shipment = $observer->getEvent()->getShipment();
			$mediaPath  = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();

			$open = "ftp://$username:$password@$host";
			// $path = $open . "/shipping slips/";
			$path = "D:/vishalxampp/htdocs/icatchers/var/shipping_slips/";
			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}

			/** @var \Magento\Sales\Model\Order $order */
			$order = $shipment->getOrder();
			$shippingAddress = $order->getShippingAddress();
			// echo $shippingAddress->getTelephone();die;
			$shipping_method = $order->getData('shipping_method');
			$sAddress = [
				'street' => $shippingAddress->getData('street'),
				'postcode' => $shippingAddress->getData('postcode'),
				'city' => $shippingAddress->getData('city'),
				'country_id' => $shippingAddress->getData('country_id'),
				'telephone' => $shippingAddress->getData('telephone')
			];

			if ($shipping_method == 'storepickup_storepickup') {
				$sAddress = [
					'street' => 'Homoetsestraat 72',
					'postcode' => '4024HJ',
					'city' => 'Eck en Wiel',
					'country_id' => 'NL',
					'telephone' => '0344-699000'
				];
			}
			
			$shippedItems = $shipment->getItemsCollection();

			$data['Opdrachtreferentie'] = $shipment->getData('increment_id');
			$data['Debiteur'] = 13515;
			$data['DebiteurNaam'] = 'I-CATCHERS';
			$data['Activiteiten']['Activiteit'] = [
				'Activiteitreferentie' => $shipment->getData('increment_id'),
				'Activiteitsoort' => 'R',
				'Losnaam' => $shippingAddress->getData('firstname'),
				'Losadres' => $sAddress['street'],
				'Lospostcode' => $sAddress['postcode'],
				'Loswoonplaats' => $sAddress['city'],
				'Lostelefoon' => $sAddress['telephone'],
				'Losland' => $sAddress['country_id'],
				'Losreferentie' => $order->getData('increment_id'),
				'Commissie' => ($order->getPayment()->getData('po_number')) ? $order->getPayment()->getData('po_number') . '/' . $order->getData('increment_id') : $order->getData('increment_id'),
				'Losemail' => $shippingAddress->getData('email'),
				'Verzendinstructie' => ($shipment->getData('customer_note')) ? $shipment->getCreatedAt() . '/' . $shipment->getData('customer_note') : $shipment->getCreatedAt()
			]; 

			foreach ($shippedItems as $item) {
				$qty = $item->getData('qty');
				$product = $this->product->load($item->getData('product_id'));
				$gr_wt = $product->getData('gr_wt');
				$num_box = $product->getData('number_of_boxes');
				$product_cbm = $product->getData('product_cbm');
				// echo $cbm;die;
				$shipmentItem[] = [
					'Aantal' => $qty,
					'Modelnaam' => trim($item->getData('name')),
					'Colli' => $qty * trim(!empty($num_box)?$num_box:0),
					'AantalZe' => '',
					'Kleur' => trim($product->getData('wood_color')),
					'Gewicht' => $qty * trim(str_replace(["Kgs","KGS","kgs"],"",!empty($gr_wt)?$gr_wt:"0 KGS")),
					'Stof' => trim($product->getData('wood_name')),
					'Volume' => str_replace(".",",",$qty * trim(str_replace(",",".",!empty($product_cbm)?$product_cbm:0))),
					'Artikelnummer' => $item->getData('sku'),
					'EANCode' => trim($product->getData('ean_code'))
				];
			}
			
			$data['Partijen'] = $shipmentItem;  
			$xmlData['Opdrachten']['Opdracht'] = $data;  
			// echo "<pre>";
			// print_r($data); exit;
			$domxml = new \DOMDocument('1.0');
			$domxml->preserveWhiteSpace = false;
			$domxml->formatOutput = true;

			$xml_data = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Bericht></Bericht>');
			$this->array_to_xml($xmlData,$xml_data);

			$fileName = $data['Opdrachtreferentie'];
			$domxml->loadXML($xml_data->asXML());
			$domxml->save($path . 'shipment_' . $fileName . '.xml');
		}	
	}

	public function array_to_xml( $data, &$xml_data ) {
		foreach( $data as $key => $value ) {
			if( is_array($value) ) {
				if( is_numeric($key) ){
					$key = 'Partij';
				}
				$subnode = $xml_data->addChild($key);
				$this->array_to_xml($value, $subnode);
			} else {
				$xml_data->addChild("$key",htmlspecialchars("$value"));
			}
		}
	}
}