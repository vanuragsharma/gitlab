<?php

namespace Yalla\Sales\Plugin\Sales\Model;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;

class OrderGet
{
    /**
     * @var OrderExtensionFactory
     */
    protected $orderExtensionFactory;

    /**
     * Init plugin
     *
     * @param OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $resultOrder
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $resultOrder
    ) {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
    	// Code to pass product name in English language only
		$order_store_id = $resultOrder->getStoreId();
		if($order_store_id != 1){
		    foreach ($resultOrder->getItems() as $item) {
		    	$product = $objectManager->create('Magento\Catalog\Model\Product')->setStoreId(\Yalla\Theme\Helper\Data::STORE_ID)->load($item->getProductId());
		    	$item->setName($product->getName());
		    }
        }
    	$paymentstatus = "";
        $payment = $resultOrder->getPayment();
        $orderDevice = $resultOrder->getOrderDevice();
        $method = $payment->getMethodInstance();
        $methodTitle = $method->getTitle();
        $methodCode = $method->getCode();
        
        $shipping = $resultOrder->getShippingAddress();
        $regionId = $shipping->getRegionId();
            
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $routeTable = $resource->getTableName('route_number');
        
        $routeNumber = 0;
        if($regionId){ 
            $select = "Select route_number From " .$routeTable. " Where region_id = " .$regionId;
            $rows = $connection->fetchAll($select);
			if(isset($rows[0]['route_number'])){
				$routeNumber = $rows[0]['route_number'];
			} 
        }
        
        $payment_method = "";
        if($methodTitle == "Cash On Delivery"){
			$payment_method = "COD - CASH ON DELIVERY";
		}

		if($methodCode == "maktapp"){
			$additionalInformations = $payment->getAdditionalInformation();
	        
			if(isset($additionalInformations["OrderUpdateData"]["PaymentStatus"])){
				$paymentstatus = $additionalInformations["OrderUpdateData"]["PaymentStatus"];
			}
			if(($paymentstatus == 'SuccessPayment' || $paymentstatus == 'success') && $methodCode == "maktapp"){
				$payment_method = "PAID ONLINE (Credit/Debit Card)";
			}else{
				$payment_method = "Credit/Debit Card - Payment Failed";
			}
		}
	
        $extensionAttributes = $resultOrder->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getGiftWrap()) {
            return $resultOrder;
        }

        /** @var \Magento\Sales\Api\Data\OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
		$orderExtension->setGiftwrap($resultOrder->getGiftwrap());
		$orderExtension->setAwOrderNote($resultOrder->getAwOrderNote());
		$orderExtension->setPaymentStatus($payment_method);
		$orderExtension->setOrderDevice($orderDevice);
		$orderExtension->setRouteNumber($routeNumber);

        $resultOrder->setExtensionAttributes($orderExtension);

        return $resultOrder;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param Collection $resultOrder
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        Collection $resultOrder
    ) {
        /** @var  $order */
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }
        return $resultOrder;
    }
}
