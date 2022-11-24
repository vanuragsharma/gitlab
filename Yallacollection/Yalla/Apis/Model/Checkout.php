<?php

namespace Yalla\Apis\Model;

use Yalla\Apis\Api\CheckoutInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;

class Checkout implements CheckoutInterface {

    protected $helper;
    protected $request;
    protected $objectManager;
    protected $scopeConfig;

    public function __construct(
    	\Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Yalla\Apis\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Quote\Model\Quote\Address\Rate $shippingRate,
        \Yalla\Apis\Helper\Data $helper,
	    \Magento\Framework\App\Request\Http $request,
	    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {

		$this->_storeManager = $storeManager;
        $this->_productFactory = $productFactory;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->shippingRate = $shippingRate;
        $this->helper = $helper;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Return Quote
     *
     * @return array
     */
    public function submitOrder() {
        $postData = file_get_contents("php://input");
		$api_auth = $this->objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        if (!empty($postData)) {
            $request = json_decode($postData, true);
        }

        if (!isset($request['customer_id'])) {
            echo json_encode(array('success' => 'false', 'msg' => 'Mandatory parameter is missing.'));
            exit;
        }
        
        if (!isset($request['payment_method']) || empty($request['payment_method'])) {
            echo json_encode(array('success' => 'false', 'msg' => 'Mandatory parameter is missing.'));
            exit;
        }
        
        $customerId = $request['customer_id'];
        $paymentMethod = $request['payment_method'];
        try {
            $customer = $this->objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => 'Customer does not exist.']);
            exit;
        }

        if (empty($customer->getId())) {
            echo json_encode(array('success' => 'false', 'msg' => 'Customer does not exist.'));
        	exit;
        }
		
        
        $store_id = 1;
        //Multi store view
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
		$apiHelper = $this->objectManager->get('Yalla\Apis\Helper\Data');
        $store_id = $apiHelper->setStore($lang, $country);
        $currency = $this->request->getParam('currency');
        if($currency) {
        	$this->_storeManager->getStore()->setCurrentCurrencyCode($currency);
        }
        //Multi store view

        $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($customerId);

        if ($store_id) {
            $quote->setStoreId($store_id);
            $quote->save();
        }
        
        $quoteId = $quote->getId();
        if (!empty($quoteId)) {
        	$data = [];
            $payment = $this->objectManager->create('\Magento\Quote\Model\Quote\Payment');
			$payment->setMethod($paymentMethod);
            $quote->setPayment($payment);

            $quote->getPayment()->importData(['method' => $paymentMethod]);
			$quote->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates();
			$quote->setInventoryProcessed(false);
			$quote->collectTotals()->save();

			if(isset($request['note'])){
				$quote->setAwOrderNote(strip_tags($request['note']));
				$quote->save();
			}
            try {
            	$order = $this->quoteManagement->placeOrder($quoteId);
        		//$order->setEmailSent(1);
				//$cart = $this->cartRepositoryInterface->get($quoteId);
				//$order_id = $this->cartManagementInterface->placeOrder($quoteId);

				//$data['order_id'] = $order_id;
				$data['order_id'] = $order;
				
				$orderRepository = $this->objectManager->create('\Magento\Sales\Api\OrderRepositoryInterface');
				$order = $orderRepository->get($order);
				$data['order_id'] = $order->getIncrementId();
				
				$billingAddress = $order->getBillingAddress();
				$currency = $order->getOrderCurrencyCode();
				$total = $order->getBaseGrandTotal();
				$subtotal = $order->getBaseSubtotal();
				$discount = $order->getData('discount_amount');
				$customer_name = $billingAddress->getData('firstname')." ".$billingAddress->getData('lastname');
				$address = $billingAddress->getData('street');
				$city = $billingAddress->getData('city');
				$region = $billingAddress->getData('region');
				$country_id = $billingAddress->getData('country_id');
				$email = $billingAddress->getData('email');
				$telephone = $billingAddress->getData('telephone');
				
				$orderItems = $order->getAllItems();
				 $totalItems = 0;
				 foreach ($orderItems as $item)
				 {
					  $totalItems = $totalItems + $item->getQtyOrdered();
				 }
				 
				if(isset($request['gift_wrap'])){
					$order->setData('giftwrap', strip_tags($request['gift_wrap']));// Fill data
				}
				
				$order->setData('order_device', 'Mobile APP');// Fill data
				$order->save();
				
				$data['Qpay_Payment'] = false;
				$data['Fatoorah_Payment'] = false;
				if($paymentMethod == 'qpaypayment'){
					$quote = $this->objectManager->create('Magento\Quote\Model\QuoteFactory')->create()->load($order->getQuoteId());
					$quote->setReservedOrderId(null);
					$quote->setIsActive(true);
					$quote->removePayment();
					$quote->save();
					
					$data['Qpay_Payment'] = true;
					$data['cart_info'] = [
						"customer_name"=> $customer_name,
						"customer_address"=> $address,
						"customer_city"=> $city,
						"customer_state"=> $region,
						"customer_country"=> $country_id,
						"customer_email"=> $email,
						"customer_currency"=> $currency,
						"customer_phone"=> $telephone,
						"cart_amount"=> $total, //float value
						"cart_Description"=>"Online Purchase from Yallatoys"
					];
				}else if($paymentMethod == 'maktapp'){
                    $token = $this->scopeConfig->getValue('payment/maktapp/merchant_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);  
                    $quote = $this->objectManager->create('Magento\Quote\Model\QuoteFactory')->create()->load($order->getQuoteId());
					$quote->setReservedOrderId(null);
					$quote->setIsActive(true);
					$quote->removePayment();
					$quote->save();
					$order->setState('pending');	
					$order->setStatus('pending');
					$order->save();
					$data['Fatoorah_Payment'] = true;
                    $data['payment_gateway_key_token'] = $token;
				}else{
					//$order->setStatus('paymentcomplete');
            		//$order->addStatusHistoryComment("Status changed.");
            		//$order->save();
					$this->objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
					$this->sendSMS($order);
				}
				$data['payment_method'] = $paymentMethod;
				$data['currency'] = $currency;
                $data['amount'] = $total;
                $data['subtotal'] = $subtotal;
                $data['totalitems'] = $totalItems;
                $data['discount'] = $discount;
                $data['customer_email'] = $email;
                $data['customer_name'] = $customer_name;
                $data['customer_phone'] = $telephone;
                $data['customer_country'] = $country_id;
				$data['order_data'] = $this->getShippingData($order);
            } catch (\Exception $e) {
                echo json_encode(array('success' => 'false', 'msg' => $e->getMessage()));
                exit;
            }
            echo json_encode(array('success' => 'true', 'msg' => 'Success', 'collection' => $data));
        	exit;
        }

        echo json_encode(array('success' => 'false', 'msg' => 'Quote does not exist'));
        exit;
    }
    
    /**
     * Update order and payment status
     *
     * @return array
     */
    public function updateStatus(){

		$api_auth =  $this->objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
    	$postData = file_get_contents("php://input");
        if (!empty($postData)) {
            $request = json_decode($postData, true);
        }
        
        if (!isset($request['orderId']) || empty($request['orderId'])) {
            echo json_encode(array('success' => 'false', 'msg' => 'Mandatory parameter is missing.'));
            exit;
        }
        
        //Multi store view
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
		$apiHelper = $this->objectManager->get('Yalla\Apis\Helper\Data');
        $store_id = $apiHelper->setStore($lang, $country);
        $currency = $this->request->getParam('currency');
        if($currency) {
        	$this->_storeManager->getStore()->setCurrentCurrencyCode($currency);
        }
        //Multi store view
        
        $order_number = $request['orderId'];
		$collection = $this->objectManager->create('Magento\Sales\Model\Order'); 
		$order = $collection->loadByIncrementId($order_number);
		$orderId = $order->getId();
		$emailCustomer = $order->getCustomerEmail();
		$orderData = $this->getShippingData($order);
		
        $status = isset($request['status']) ? $request['status'] : 'canceled';
        $transactionStatus = isset($request['transactionStatus']) ? $request['transactionStatus'] : 'canceled';
        $transactionId = isset($request['transactionId']) ? $request['transactionId'] : '';
        $maskedCardNumber = isset($request['maskedCardNumber']) ? $request['maskedCardNumber'] : '';
        $datetime = isset($request['datetime']) ? $request['datetime'] : '';
        $cardType = isset($request['cardType']) ? $request['cardType'] : '';
        $amount = isset($request['amount']) ? $request['amount'] : '';
        $reason = isset($request['reason']) ? $request['reason'] : '';
        $paymentgateway = isset($request['paymentgateway']) ? $request['paymentgateway'] : '';
        $paymentId = isset($request['paymentId']) ? $request['paymentId'] : '';
        $trackId = isset($request['trackId']) ? $request['trackId'] : '';
        
        $message = '';
		$message .= 'TransactionId- ' . $transactionId . "<br/>";
		$message .= 'Amount- ' . $amount . "<br/>";
		$message .= 'Payment Status- ' . $status . "<br/>";
		$message .= 'TransactionStatus- ' . $transactionStatus . "<br/>";
		$message .= 'MaskedCardNumber: ' . $maskedCardNumber . "<br/>";
		$message .= 'Datetime: ' . $datetime . "<br/>";
		$message .= 'CardType: ' . $cardType . "<br/>";
                $message .= 'paymentgateway: ' . $paymentgateway . "<br/>";
                $message .= 'paymentId: ' . $paymentId . "<br/>";
                $message .= 'trackId: ' . $trackId . "<br/>";
                
		if(!empty($reason)){
			$message .= 'Reason: ' . $reason . "<br/>";
		}
		
		$UpdateApidata = array("TransactionId"=>$transactionId, "Amount"=>$amount, "PaymentStatus"=>$status, "TransactionStatus"=>$transactionStatus, "MaskedCardNumber"=>$maskedCardNumber, "Datetime"=>$datetime, "CardType"=>$cardType, "paymentgateway"=>$paymentgateway, "paymentId"=>$paymentId, "trackId"=>$trackId);
		$transactionBuilder = $this->objectManager->create('\Magento\Sales\Model\Order\Payment\Transaction\Builder');
		$payment = $order->getPayment();
        if($status == 'SuccessPayment'){
		    $payment->setTransactionId($transactionId);
		    $payment->setLastTransId($transactionId);
		    $payment->setAdditionalInformation(['OrderUpdateData' => (array) $UpdateApidata]);
		    if($maskedCardNumber){
		    	$card_numbers = explode("-", $maskedCardNumber);
		    	if(isset($card_numbers[3])){
		    		$payment->setCcLast4($card_numbers[3]);
		    	}
		    }
		    
	   		//$orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
	        $orderStatus = $order->getConfig()->getStateDefaultStatus('paymentcomplete');
	        
			$transaction = $transactionBuilder->setPayment($payment)->setOrder($order)->setTransactionId($transactionId)->setFailSafe(true)->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

			$payment->addTransactionCommentsToOrder($transaction, $message);
			$payment->setParentTransactionId(null);
			$payment->save();
			
			$order->setTotalpaid($order->getBaseGrandTotal());
			//$order->setStatus('paymentcomplete');
            $order->addStatusHistoryComment("Payment Received.");
			$order->save();
			$transaction->save();
			
			//$this->objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
	        $this->sendSMS($order);
			// Create invoice
	        /*$invoiceSender = $this->objectManager->get('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');

		    if (!$order->hasInvoices() && $order->canInvoice()) {
	       		$invoice = $order->prepareInvoice();
	       		if ($invoice->getTotalQty() > 0) {
		           $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
		           $invoice->setTransactionId($order->getPayment()->getTransactionId());
		           $invoice->register();
		           $invoice->addComment(__('Automatic invoice.'), false);
		           $invoice->save();
		           $invoiceSender->send($invoice);
	       		}
		    }*/
	        
	        $quote = $this->objectManager->create('Magento\Quote\Model\QuoteFactory')->create()->load($order->getQuoteId());
	        $quote->setIsActive(false);
			$quote->setReservedOrderId(null);
			$quote->save();
		}else{
			$transaction = $transactionBuilder->setPayment($payment)->setOrder($order)->setTransactionId($transactionId)->setFailSafe(true)->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID);

			$payment->addTransactionCommentsToOrder($transaction, $message);
			$payment->setParentTransactionId(null);
			$payment->save();

			$payment->setStatus('CANCEL');
			$payment->setShouldCloseParentTransaction(1)->setIsTransactionClosed(1);
			$payment->save();
	    
			$order->addStatusHistoryComment($message, \Magento\Sales\Model\Order::STATE_CANCELED);

			$order->cancel()->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true, 'Transaction is canceled!');

			$quote = $this->objectManager->create('Magento\Quote\Model\QuoteFactory')->create()->load($order->getQuoteId());
			$quote->setReservedOrderId(null);
			$quote->setIsActive(true);
			$quote->removePayment();
			$quote->save();
			$order->save();
            echo json_encode(array('success' => 'true', 'status' => 'fail','msg' => 'Order status has been failed try again.'));
        exit;
		}
		
		echo json_encode(array('success' => 'true', 'status' => 'success', 'collection' => $orderData, 'msg' => 'Order status has been updated.'));
        exit;
    }

	private function sendSMS($order){
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
				
				 $enable = $this->scopeConfig->getValue('Homepage/sendsms/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		         $senderId = $this->scopeConfig->getValue('Homepage/sendsms/senderid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		         $sId = $this->scopeConfig->getValue('Homepage/sendsms/sid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		         $apiKey = $this->scopeConfig->getValue('Homepage/sendsms/apikey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		           
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
	
	public function getShippingData($order){
          $billing_address = array();
          $shipping_address = array();
          $orderItems = $order->getAllVisibleItems();
          $discount = str_replace("-", "", $order->getGrandTotal() - $order->getSubtotal());
          $order_items = $this->helper->cartItemsFormat($orderItems, true);
          $pay_amount = $order->getGrandTotal();
          $payment = $order->getPayment();
	  $method = $payment->getMethodInstance();
	  $methodTitle = $method->getTitle();
          $methodCode = $method->getCode();
          $billingAddress = $order->getBillingAddress();
          $billingAddress = $billingAddress->getData();          
          $shippingAddress = $order->getShippingAddress();
          $shippingAddress = $shippingAddress->getData();
          $billing_address = [
                'firstname' => (isset($billingAddress['firstname']) ? $billingAddress['firstname'] : ''),
                'lastname' => (isset($billingAddress['lastname']) ? $billingAddress['lastname'] : ''),
                'street' => (isset($billingAddress['street']) ? $billingAddress['street'] : ''),
                'city' => (isset($billingAddress['city']) ? $billingAddress['city'] : ''),
                'country_id' => (isset($billingAddress['country_id']) ? $billingAddress['country_id'] : ''),
                'region' => (isset($billingAddress['region']) ? $billingAddress['region'] : ''),
                'region_id' => (isset($billingAddress['region']) ? $billingAddress['region_id'] : ''),
                'zone' => (isset($billingAddress['zone']) ? $billingAddress['zone'] : ''),
                'building_number' => (isset($billingAddress['building_number']) ? $billingAddress['building_number'] : ''),
                'landmark' => (isset($billingAddress['landmark']) ? $billingAddress['landmark'] : ''),
                'telephone' => (isset($billingAddress['telephone']) ? $billingAddress['telephone'] : ''),
                'postcode' => (isset($billingAddress['postcode']) ? $billingAddress['postcode'] : ''),
                'quote_address_id' => (isset($billingAddress['quote_address_id']) ? $billingAddress['quote_address_id'] : '')
            ];

          $shipping_address = [
                'firstname' => (isset($shippingAddress['firstname']) ? $shippingAddress['firstname'] : ''), //address Details
                'lastname' => (isset($shippingAddress['lastname']) ? $shippingAddress['lastname'] : ''),
                'street' => (isset($shippingAddress['street']) ? $shippingAddress['street'] : ''),
                'city' => (isset($shippingAddress['city']) ? $shippingAddress['city'] : ''),
                'country_id' => (isset($shippingAddress['country_id']) ? $shippingAddress['country_id'] : ''),
                'region' => (isset($shippingAddress['region']) ? $shippingAddress['region'] : ''),
                'region_id' => (isset($shippingAddress['region_id']) ? $shippingAddress['region_id'] : ''),
                'zone' => (isset($shippingAddress['zone']) ? $shippingAddress['zone'] : ''),
                'building_number' => (isset($shippingAddress['building_number']) ? $shippingAddress['building_number'] : ''),
                'landmark' => (isset($shippingAddress['landmark']) ? $shippingAddress['landmark'] : ''),
                'postcode' => (isset($shippingAddress['postcode']) ? $shippingAddress['postcode'] : ''),
                'telephone' => (isset($shippingAddress['telephone']) ? $shippingAddress['telephone'] : ''),
                'quote_address_id' => (isset($shippingAddress['quote_address_id']) ? $shippingAddress['quote_address_id'] : '')
            ];
         
           $coupon_code = $order->getCouponCode();
           $tax = $order->getTaxAmount();
           $shipping_amount = $order->getShippingAmount();
           
			return array('billing_address' => $billing_address, 'shipping_address' => $shipping_address, 'coupon_code' => $coupon_code, 'tax' => $tax, 'shipping_amount' => $shipping_amount,'methodTitle' => $methodTitle, 'pay_amount' => $pay_amount, 'discount_amount_without_currency' => $discount, 'methodCode' => $methodCode, 'orderItems' => $order_items);
    }
}
