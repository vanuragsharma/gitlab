<?php

 namespace Yalla\Theme\Observer; 
use Magento\Framework\Event\ObserverInterface; 
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class SalesAfter implements ObserverInterface { 
 
    protected $customerRepository;
    protected $addressRepositoryInterface; 
    protected $_request;
	protected $scopeConfig;
	
    public function __construct( CustomerRepositoryInterface $customerRepository,AddressRepositoryInterface $addressRepositoryInterface,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
\Magento\Framework\App\RequestInterface $request) { 
        $this->customerRepository = $customerRepository;
        $this->addressRepositoryInterface = $addressRepositoryInterface;
        $this->scopeConfig = $scopeConfig;
        $this->_request = $request;
    }
 
	private function getUpdatedStreet($order_street, $street_address){
 		
 		$street = "";
		$street_addressline1 = "";
 		if(is_array($order_street)){
 			$street_address_concat = [];
			if(strpos($street_address[0], 'St:') === false) {
				$street = 'St: '.$street_address['0'];
			}
			if(isset($street_address[1])){
				if(strpos($street_address[1], 'Bld:') === false && !empty($street_address['1'])) {
					$street_addressline1 = 'Bld: '.$street_address['1'];
				}
			}
			
			$street_address_concat = array($street,$street_addressline1);
		}else{
			$street_address_concat = '';
			if(strpos($order_street, 'St:') === false) {
				$street = 'St:'.$street_address['0'];
			}
			if(isset($street_address[1])){
				if(strpos($order_street, 'Bld:') === false && !empty($street_address['1'])) {
				  $street .= " ".'Bld:'.$street_address['1'];
				}
			}
			$street = $street_address_concat;
		}
		if(!empty($street)){
			return $street_address_concat;
		}else{
			return false;
		}
 	}
 	 
    public function execute(\Magento\Framework\Event\Observer $observer) { 
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
      $customerSession = $objectManager->get('Magento\Customer\Model\Session');
      
      //Concatenate street and Addressline in shipping address
		$order = $observer->getEvent()->getOrder();
		$customer_address_id = $order->getShippingAddress()->getCustomerAddressId();

		if($customer_address_id){
			$addressRepository = $this->addressRepositoryInterface->getById($customer_address_id);  
			$street_address = $addressRepository->getStreet();
			$order_street = $order->getShippingAddress()->getStreet();

			$street_address_concat = $this->getUpdatedStreet($order_street, $street_address);
			
			if($street_address_concat){
				$order->getShippingAddress()->setStreet($street_address_concat)->save();
			}
		}else{			
			$street_address = $order->getShippingAddress()->getStreet();
			$street_address_concat = $this->getUpdatedStreet($street_address, $street_address);

			if($street_address_concat){
				$order->getShippingAddress()->setStreet($street_address_concat)->save();
			}
		}

		$customer_address_id = $order->getBillingAddress()->getCustomerAddressId();
		if($customer_address_id){
			$street_address_concat = '';
			$addressRepository = $this->addressRepositoryInterface->getById($customer_address_id);  
			$street_address = $addressRepository->getStreet();

			$order_street = $order->getBillingAddress()->getStreet();
			$street_address_concat = $this->getUpdatedStreet($order_street, $street_address);
			
			if($street_address_concat){
				$order->getBillingAddress()->setStreet($street_address_concat)->save();
			}
		}else{
			$street_address = $order->getBillingAddress()->getStreet();
			$street_address_concat = $this->getUpdatedStreet($street_address, $street_address);
			
			if($street_address_concat){
				$order->getBillingAddress()->setStreet($street_address_concat)->save();
			}
		}

      if($customerSession->isLoggedIn()) {

           $order = $observer->getEvent()->getOrder();
           $allItems = $order->getAllItems();
	       $quote_has_preorder = false;
		   // iterate all cart products to check if no_free_shipping is true
		   $itemHelper = $objectManager->create('Mageplaza\PreOrder\Helper\Item');
		   foreach ($allItems as $item) {    
			   $isPreOrder = $itemHelper->isApplyForProduct($item->getProduct());
			   if ($isPreOrder) {
			       $quote_has_preorder = true;
			   }
		   }
        
			if($quote_has_preorder){
				$address_id = $order->getShippingAddress()->getData('entity_id');
				$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
				$connection = $resource->getConnection();
				$table = $resource->getTableName('mageworx_dd_queue');

				$connection->delete(
					$table,
					['order_address_id = ?' => $address_id]
				);
			}
           $customerId = $order->getCustomerId(); 
           
           $payment = $order->getPayment();
			$method = $payment->getMethodInstance();
			$paymentCode = $method->getCode();
           if($customerId){
		       $customer = $this->customerRepository->getById($customerId);

				$name = $customer->getFirstname()." ".$customer->getLastname();
		       if($customer->getCustomAttribute('mobile_number'))
		        {
		            $mobile = $customer->getCustomAttribute('mobile_number')->getValue();
		        }else{
		         $mobile = '';
		        }
		        
		        /*if($method != 'cashondelivery' && $order->getStatus() == 'pending'){
		        	$mobile = '';
		        }*/
		        if($order->getStatus() != 'pending' && $order->getStatus() != 'paymentcomplete'){
		        	$mobile = '';
		        }

		        if($paymentCode == 'qpaypayment' && $order->getStatus() == 'pending'){
		        	$mobile = '';
		        }
		       $orderid = $order->getIncrementId();
		       $message = "Thank You for shopping with Yallatoys. Your order: $orderid is under process. We will let you know when its confirmed.";

		       if(!empty($mobile)){
		    		/*$cURLConnection = curl_init();
		    		
		    		$host = "http://world.msg91.com/api/sendhttp.php?authkey=7399AMeEmlLk5dBp5e8caecbP123&mobiles=$mobile&message=$message&sender=YALLATOYS&route=4&country=0";
		    		curl_setopt($cURLConnection, CURLOPT_URL, $host);
		    		curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

		    		$response = curl_exec($cURLConnection);
		    		curl_close($cURLConnection);*/
		    		
		    		 $enable = $this->scopeConfig->getValue('Homepage/sendsms/enable', ScopeInterface::SCOPE_STORE);
				     $senderId = $this->scopeConfig->getValue('Homepage/sendsms/senderid', ScopeInterface::SCOPE_STORE);
				     $sId = $this->scopeConfig->getValue('Homepage/sendsms/sid', ScopeInterface::SCOPE_STORE);
				     $apiKey = $this->scopeConfig->getValue('Homepage/sendsms/apikey', ScopeInterface::SCOPE_STORE);
				       
				     if($enable == 1 && !empty($senderId) && !empty($sId) && !empty($apiKey)){
				     
		   	         $ch = curl_init(); 
		   	       
				     /* $curlRequest = json_encode($curlConfig); */   
				    
						 $template_id = "123456".rand(100000,100000);
				     if(strpos($mobile, '+91') !== false)
				     {
				     	$postdata = "to=$mobile&type=OTP&sender=$senderId&body=$message&template_id=$template_id";
				     }else{
				     	$postdata = "to=$mobile&type=OTP&sender=$senderId&body=$message";
				     }
				        curl_setopt($ch, CURLOPT_URL, 'https://api.kaleyra.io/v1/'.$sId.'/messages'); // SID
				        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
				        curl_setopt($ch, CURLOPT_POST, true); 
				        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
				        curl_setopt($ch, CURLOPT_HTTPHEADER, array('api-key:'.$apiKey,'Content-Type: application/x-www-form-urlencoded'));  
				      
				     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				     $response = curl_exec ($ch);

				     curl_close ($ch); 

				     $curlRequest = json_decode($response,true);
			 
				 // var_dump($curlRequest);
					 if (count($curlRequest['error']) > 0 ) {
				          //return false;
				     } else {
				          //return true;
				     }
				      
				    }
			   }
	       }

       } 
     
    }
}
