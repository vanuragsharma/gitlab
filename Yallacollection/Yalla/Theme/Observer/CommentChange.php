<?php

 namespace Yalla\Theme\Observer; 
use Magento\Framework\Event\ObserverInterface; 
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class CommentChange implements ObserverInterface { 
 
    protected $customerRepository; 
    protected $orderRepository;
	protected $dataHelper;
	protected $scopeConfig;
	
    public function __construct( CustomerRepositoryInterface $customerRepository, \Magento\Sales\Model\OrderRepository $orderRepository,
    \Yalla\Theme\Helper\Data $dataHelper,\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) { 
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->dataHelper = $dataHelper;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer) { 
        
        $orderData = $observer->getEvent()->getStatusHistory();
        $post = $observer->getEvent();
        $IsNotified = $orderData->getIsCustomerNotified();
        
        $orderId = $orderData->getParentId();
		$order = $this->orderRepository->get($orderId);
        if($order->getStatus() == 'delivrd'){
			$trackEvent = $this->dataHelper->webEngageTrackEvent('delivered', $order);
		}
        if($IsNotified){
			$comment = $orderData->getComment(); 
			
			$customerId = $order->getCustomerId();

			if($customerId){
				$customer = $this->customerRepository->getById($customerId);   
				$name = $customer->getFirstname()." ".$customer->getLastname();
				
				$mobile_attr = $customer->getCustomAttribute('mobile_number');
				if($mobile_attr){
					$mobile = $mobile_attr->getValue();
					$orderid = $order->getIncrementId();
					
					$message = '';
					if($order->getStatus() == 'pending'){
						$message = "Hi $name, your Yallatoys order $orderid has been placed. We will let you know once the payment is successfull.";
					}
					if($order->getStatus() == 'disptd'){
						$message = "Hi $name, your Yallatoys order :$orderid has been Confirmed. We will let you know when it is Dispatched";
					}
					if($order->getStatus() == 'dispchd'){
						$message = "Hi $name, your Yallatoys order :$orderid has been Dispatched.";
					}
					if($order->getStatus() == 'delivrd'){
						$message = "Hi $name, your Yallatoys order :$orderid has been Delivered.";
					}
					if($order->getStatus() == 'canceled'){
						$message = "Hi $name, your Yallatoys order :$orderid has been Cancelled.";
					}
				  
					if(!empty($mobile) && !empty($message)){
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
						      return false;
						  } else {
						      return true;
						  }
						  
						}
					}
				} 
			} 
		}
    
    }
}
