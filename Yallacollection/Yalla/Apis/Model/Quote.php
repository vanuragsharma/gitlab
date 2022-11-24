<?php

namespace Yalla\Apis\Model;

use Yalla\Apis\Api\QuoteInterface;
use Magento\Framework\App\ObjectManager;
use DateTime;
use MageWorx\DeliveryDate\Api\DeliveryManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Quote implements QuoteInterface {

    protected $helper;
    protected $request;
    protected $objectManager;
    
    /**
     * @var DeliveryManagerInterface
     */
    private $deliveryManager;

    /**
     * @var TimezoneInterface
     */
    private $timezone;
    
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    
    public function __construct(
            \Magento\Quote\Model\QuoteFactory $quoteFactory,
            \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
            \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
            \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            DeliveryManagerInterface $deliveryManager,
            TimezoneInterface $timezone,
            \Yalla\Apis\Helper\Data $helper,
            SerializerInterface $serializer,
            \Magento\Framework\App\Request\Http $request) {

        $this->_storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->customerRepository = $customerRepository;
        $this->deliveryManager = $deliveryManager;
        $this->timezone = $timezone;
        $this->helper = $helper;
        $this->request = $request;
        $this->serializer     = $serializer;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Return Quote
     *
     * @return array
     */
    public function quoteReview() {

        $postData = file_get_contents("php://input");
		$api_auth =  $this->objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
		
        if (!empty($postData)) {
            $request = json_decode($postData, true);
        }
        
        if (!isset($request['app_version'])) {
            echo json_encode(array('success' => 'false', 'msg' => __('Please update the App, New version available in Store')));
            exit;
        }

        if (!isset($request['customer_id'])) {
            echo json_encode(array('success' => 'false', 'msg' => 'Mandatory parameter is missing.'));
            exit;
        }
        if (!isset($request['billing_address_id'])) {
            echo json_encode(array('success' => 'false', 'msg' => 'Mandatory parameter is missing.'));
            exit;
        }
        if (!isset($request['shipping_address_id'])) {
            echo json_encode(array('success' => 'false', 'msg' => 'Mandatory parameter is missing.'));
            exit;
        }

        $customerId = $request['customer_id'];
        
        try {
            $customer = $this->objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => 'Customer does not exist.']);
            exit;
        }

        if (empty($customer->getId())) {
            $response[] = ['success' => 'false', 'message' => 'Customer does not exist.'];
            return $response;
        }
        
        $shipping_method = '';
        if (isset($request['shipping_method']) && !empty($request['shipping_method'])) {
			$shipping_method = $request['shipping_method'];
        }
        
        $billingAddressId = (isset($request['billing_address_id']) ? $request['billing_address_id'] : 0);
        $shippingAddressId = (isset($request['shipping_address_id']) ? $request['shipping_address_id'] : 0);

        //Multi store view
        $store_id = 1;
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
        
        	$quote->setCouponCode('APPONLY10')->collectTotals()->save();
        	
            $quoteItems = $quote->getAllVisibleItems();

            // Check if quote has no items
            $quote_items = $this->helper->cartItemsFormat($quoteItems); //this is inside helper data
            if (!count($quote_items)) {
                echo json_encode(['success' => 'false', 'msg' => __("Your cart is empty.")]);
                exit;
            }

            try {
                $billing_address = array();
                $shipping_address = array();

                $addressManagement = $this->objectManager->create('Yalla\Apis\Model\AddressManagement');

                if (!$billingAddressId) {
                    $billingAddressId = $customer->getDefaultBilling();
                }
                if ($billingAddressId) {
                    try {
                        $billingAddress = $addressManagement->getCustomerAddressById($billingAddressId);
                        $billing_address = $addressManagement->getAddressData($billingAddress);
                    } catch (\Exception $e) {
                        echo json_encode(['success' => 'false', 'msg' => __("Error with billing address.")]);
                        exit;
                    }
                }
                if (!$shippingAddressId) {
                    $shippingAddressId = $customer->getDefaultShipping();
                }
                if ($shippingAddressId) {
                    try {
                        $shippingAddress = $addressManagement->getCustomerAddressById($shippingAddressId);
                        $shipping_address = $addressManagement->getAddressData($shippingAddress);
                    } catch (\Exception $e) {
                        echo json_encode(['success' => 'false', 'msg' => __("Error with shipping address.")]);
                        exit;
                    }
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => 'false', 'msg' => $e->getMessage()]);
                exit;
            }

			$shippingAddress = $quote->getShippingAddress();
			
			$extensionAttributes = $shippingAddress->getExtensionAttributes();
			$deliveryDay = $extensionAttributes->getDeliveryDay();
			//var_dump($deliveryDay);
			
			//var_dump($shippingAddress->getData(\MageWorx\DeliveryDate\Api\Data\QueueDataInterface::DELIVERY_DAY_KEY));
			
            $billing_address = [
                'firstname' => (isset($billing_address['firstname']) ? $billing_address['firstname'] : ''),
                'lastname' => (isset($billing_address['lastname']) ? $billing_address['lastname'] : ''),
                'street' => (isset($billing_address['street']) ? $billing_address['street'] : ''),
                'city' => (isset($billing_address['city']) ? $billing_address['city'] : ''),
                'country_id' => (isset($billing_address['country_id']) ? $billing_address['country_id'] : ''),
                'region' => (isset($billing_address['region']) ? $billing_address['region'] : ''),
                'region_id' => (isset($billing_address['region']) ? $billing_address['region_id'] : ''),
                'zone' => (isset($billing_address['zone']) ? $billing_address['zone'] : ''),
                'building_number' => (isset($billing_address['building_number']) ? $billing_address['building_number'] : ''),
                'landmark' => (isset($billing_address['landmark']) ? $billing_address['landmark'] : ''),
                'telephone' => (isset($billing_address['telephone']) ? $billing_address['telephone'] : ''),
                'postcode' => (isset($billing_address['postcode']) ? $billing_address['postcode'] : ''),
                'customer_address_id' => $billingAddressId
            ];

			$country_id = isset($shipping_address['country_id']) ? $shipping_address['country_id'] : '';

            $shipping_address = [
                'firstname' => (isset($shipping_address['firstname']) ? $shipping_address['firstname'] : ''), //address Details
                'lastname' => (isset($shipping_address['lastname']) ? $shipping_address['lastname'] : ''),
                'street' => (isset($shipping_address['street']) ? $shipping_address['street'] : ''),
                'city' => (isset($shipping_address['city']) ? $shipping_address['city'] : ''),
                'country_id' => (isset($shipping_address['country_id']) ? $shipping_address['country_id'] : ''),
                'region' => (isset($shipping_address['region']) ? $shipping_address['region'] : ''),
                'region_id' => (isset($shipping_address['region_id']) ? $shipping_address['region_id'] : ''),
                'zone' => (isset($billing_address['zone']) ? $billing_address['zone'] : ''),
                'building_number' => (isset($billing_address['building_number']) ? $billing_address['building_number'] : ''),
                'landmark' => (isset($billing_address['landmark']) ? $billing_address['landmark'] : ''),
                'postcode' => (isset($shipping_address['postcode']) ? $shipping_address['postcode'] : ''),
                'telephone' => (isset($shipping_address['telephone']) ? $shipping_address['telephone'] : ''),
                'customer_address_id' => $shippingAddressId
            ];

            $quote->getBillingAddress()->addData($billing_address);
            $quote->getShippingAddress()->addData($shipping_address);

            $paymentMethodManagement = $this->objectManager->get('Magento\Quote\Api\PaymentMethodManagementInterface');
            $paymentMethods = $paymentMethodManagement->getList($quoteId);
			
			$scopeConfig = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');            
            //Add gift_wrap
            $giftwrap =  $scopeConfig->getValue('Homepage/giftwrap/enable');
            $data['gift_wrap'] = empty($giftwrap) ? 0 : $giftwrap;
            
            $data['payment_methods'] = array();

            $grand_total = number_format($quote->getBaseGrandTotal(), 4);

            if (!empty($paymentMethods)) {
                foreach ($paymentMethods as $row) {
                    if ($grand_total == 0 && $row->getCode() != 'free')
                        continue;

					
					if(!isset($request['app_version']) && $row->getCode() == "maktapp"){
						continue;
					}
                    $d = [
                        'title' => $row->getTitle(),
                        'code' => $row->getCode(),
                    ];
                    array_push($data['payment_methods'], $d);
                }
            }
            if(empty($shipping_method)){
				$shipping_method = empty($shippingAddress->getShippingMethod()) ? $shippingAddress->getShippingMethod() : '';
				
			}
            $shipping = $this->calculateShipping($quote, $shipping_method);
            $method = $shipping['method'];
            $shipping_rate = $shipping['rate'];
            $quote = $shipping['updated_quote'];
           
            $shipping_methods = $this->getShippingMethod($quote);	
					
			//print_r($shipping_methods);
			$status = 'true';
			$message = 'Success';
			if(!count($shipping_methods)){
				$status = 'false';
				$message = __('At present we deliver only in Qatar, please select a valid address');
			}
			
			$shippingAddress = $quote->getShippingAddress();
			
			$extensionAttributes = $shippingAddress->getExtensionAttributes();
			$deliveryDay = $extensionAttributes->getDeliveryDay();
			
			$shippingMethod = $shippingAddress->getShippingMethod();
			if($shippingMethod){
				/*$currentDayTime = $this->getCurrentDateTime();
		        $deliveryOption = $this->findDeliveryOption($quote, $shippingMethod, $currentDayTime);
		        $dayLimits      = $deliveryOption->getDayLimits();
		        if (empty($dayLimits)) {
		            echo json_encode(['success' => 'false', 'msg' => 'Selected shipping method is not available.']);
                	exit;
		        }
		        
		        $firstAvailableLimit = array_shift($dayLimits);
		        $extension = $shippingAddress->getExtensionAttributes();

		        $extension->setDeliveryDay($firstAvailableLimit['date']);
		        $extension->setDeliveryOptionId($deliveryOption->getId());
		        $shippingAddress->setExtensionAttributes($extension);
		        
		        $quote->getShippingAddress()->addData($shippingAddress->getData());
		        $quote->save();*/
            }
            
            $tax = $shippingAddress->getTaxAmount();
            
            $discount_amount = $quote->getBaseSubtotal() - $quote->getBaseSubtotalWithDiscount();

            $coupon_code = $quote->getCouponCode();
            $is_coupon_applied = 0;
            if (!empty($coupon_code)) {
                $is_coupon_applied = 1;
            }

            $data['quoteId'] = $quoteId;
            $data['product_count'] = $quote->getItemsQty();
            $data['items'] = $quote_items;
            $data['billing_address'] = count($billing_address) ? $billing_address : array();
            $data['shipping_address'] = count($shipping_address) ? $shipping_address : array();
            $data['shipping_method_code'] = $method;
            $data['shipping_methods'] = $shipping_methods;
            
            $data['donations'] = $this->helper->getDonations();

			$balance = $this->objectManager->create('Mirasvit\Rewards\Helper\Balance');
            $data['cart_points'] = $this->cartRewardPoints($quote);
			$data['user_points'] = $balance->getBalancePoints($customer);
			
			$purchaseHelper = $this->objectManager->get('\Mirasvit\Rewards\Helper\Purchase');
			$purchase = $purchaseHelper->getByQuote($quote->getId());
			$data['spend_points'] = (int) $purchase->getSpendPoints();
			
			$data['points'] = $this->pointsData($quote, $customer);

			$priceHelper = $this->objectManager->get('Magento\Framework\Pricing\Helper\Data');

			$data['applied_donation'] = new \Magento\Framework\DataObject();

			$quote_donation = $quote->getShippingAddress()->getMageworxDonationDetails();
			if(!empty($quote_donation)){
				$donationDetails = $this->serializer->unserialize($quote_donation);
				if(isset($donationDetails['donation'])){
					$donation_value = isset($donationDetails['donation']) ? $donationDetails['donation'] : 0;
					
					$data['applied_donation'] = [];
					$data['applied_donation'] = [
						"donation" => (string) $donation_value,
						"donation_label" => (string) "+".$priceHelper->currency($donation_value, true, false),
						"charity_id" => "1",
						"charity_title" => "Education Above All"
					];
				}
			}

			$discount_without_currency = $quote->getGrandTotal() - $quote->getSubtotal();
            $data['summary'] = [
                'total' => $priceHelper->currency($quote->getBaseGrandTotal(), true, false),
                'sub_total' => $priceHelper->currency($quote->getBaseSubtotal(), true, false),
                'discount_amount' => "-".$priceHelper->currency($discount_amount, true, false),
                'total_without_currency' => str_replace(",", "", number_format($quote->getGrandTotal(),2)),
                'discount_amount_without_currency' => str_replace("-", "", number_format($discount_without_currency,2)),
				'subtotal_with_discount' => str_replace(",", "", number_format($quote->getSubtotalWithDiscount(),2)),
                'sub_total_without_currency' => str_replace(",", "", number_format($quote->getSubtotal(),2)),
                'is_coupon_added' => $is_coupon_applied,
                'coupon_code' => (empty($coupon_code) ? '' : $coupon_code),
                'shipping_amount' => "+".$priceHelper->currency($shipping_rate, true, false),
                'tax' => (string) $priceHelper->currency($tax, true, false)
            ];
        }

        echo json_encode(array('success' => $status, 'msg' => $message, 'collection' => $data));
        exit;
    }
    
    /**
     * @return DateTime
     * @throws \Exception
     */
    public function getCurrentDateTime()
    {
        $currentDayTime = new DateTime();
        $storeTimeZone  = new \DateTimeZone($this->timezone->getConfigTimezone());
        $currentDayTime->setTimezone($storeTimeZone);

        return $currentDayTime;
    }

    /**
     * @param Quote $quote
     * @param string $shippingMethod
     * @param DateTime $currentDayTime
     * @return DeliveryOption
     * @throws \Exception
     */
    public function findDeliveryOption(\Magento\Quote\Model\Quote $quote, string $shippingMethod, DateTime $currentDayTime)
    {
        $this->deliveryManager->setDaysOffset($this->deliveryManager->calculateDaysOffset($quote));
        $customerGroupId = $quote->getCustomer()->getGroupId();
        $storeId         = $quote->getStoreId();
        /** @var DeliveryOption $deliveryOption */
        $deliveryOption = $this->deliveryManager->getDeliveryOptionForMethod(
            $shippingMethod,
            $currentDayTime,
            $customerGroupId,
            $storeId
        );

        return $deliveryOption;
    }

    public function calculateShipping($quote, $method = '') {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $quote->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates();
        $quote->collectTotals()->save();
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load($quote->getId());


        $_rates = $quote->getShippingAddress()->getShippingRatesCollection();

        $shippingRates = array();

		if (empty($method)) {
		    foreach ($_rates as $_rate) {
		        if ($_rate->getCode() == 'freeshipping_freeshipping') {
		            $method = $_rate->getCode();
		            break;
		        }
		    }
		}
		
        $shipping_rate = 0;
        if (!empty($method)) {
            $quote->getShippingAddress()->setCollectShippingRates(true)
                    ->collectShippingRates()
                    ->setShippingMethod($method);

            $rate = $quote->getShippingAddress()->getShippingRateByCode($method);
            try {
                $shipping_rate = (!empty($rate)) ? $rate->getPrice() : 0;
            } catch (\Exception $e) {
                $shipping_rate = 0;
            }
        }
        $quote->collectTotals()->save();
        
        return array('method' => !empty($method) ? $method : '', 'rate' => $shipping_rate, 'updated_quote' => $quote);	
    }
    
    public function getShippingMethod($quote){
    	$_rates = $quote->getShippingAddress()->getShippingRatesCollection();
    	
    	$methods = [];
    	foreach ($_rates as $_rate) {
            $methods[$_rate->getCode()] = [
            	'shipping_title' => $_rate->getData('carrier_title'),
            	'shipping_code' => $_rate->getCode(),
            	'shipping_cost' => $_rate->getPrice()
            ];
        }

        $deliveryManager = $this->objectManager->create('MageWorx\DeliveryDate\Model\DeliveryManager');
		$delivery_methods = $deliveryManager->getAvailableLimitsForQuote($quote);
		
		foreach($delivery_methods as $code => $delivery_method){
			if(isset($methods[$code])){
				if(isset($delivery_method['day_limits']) && is_array($delivery_method['day_limits']) && count($delivery_method['day_limits'])){
					foreach($delivery_method['day_limits'] as $day){
						$methods[$code]['expected_delivery_date'] = (isset($day['date']) ? $day['date'] : '');
						break;
					}
				}else {
					$methods[$code]['expected_delivery_date'] = '';
				}
			}
		}

		// Remove unavailable methods
		foreach($methods as $key => $method){
			if(empty($method['expected_delivery_date']) && $key != 'freeshipping_freeshipping'){
				unset($methods[$key]);
			}
			if($key == 'freeshipping_freeshipping'){
				unset($methods['flatrate_flatrate']);
			}
		}
		
        
        $methods = array_values($methods);
        return $methods;
    }

    /**
     * Apply Coupon
     *
     * @param int $quote_id
     * @param string $coupon_code
     * @return array
     */
    public function redeemCoupon() {

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		

		$api_auth = $this->objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        //Multi store view
        $store_id = 1;
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
		$apiHelper = $this->objectManager->get('Yalla\Apis\Helper\Data');
        $store_id = $apiHelper->setStore($lang, $country);
        $currency = $this->request->getParam('currency');
        if($currency) {
        	$this->_storeManager->getStore()->setCurrentCurrencyCode($currency);
        }
        //Multi store view
        
		$postData = file_get_contents("php://input");
        if (!empty($postData)) {
            $request = json_decode($postData, true);
        }

		if (!isset($request['quote_id'])) {
		    echo json_encode(array('success' => 'false', 'msg' => 'Mandatory parameter is missing.'));
		    exit;
		}
		if (!isset($request['coupon_code'])) {
		    echo json_encode(array('success' => 'false', 'msg' => 'Mandatory parameter is missing.'));
		    exit;
		}
		
		$quote_id = $request['quote_id'];
		$coupon_code = $request['coupon_code'];
        try {
            try {
                $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load($quote_id);
            } catch (\Exception $e) {
                echo json_encode(array('success' => 'false', 'msg' => __("Cart does not exist!")));
                exit;
            }

            if (!$quote->getItemsCount()) {
                echo json_encode(array('success' => 'false', 'msg' => __("Cart does not contain products!")));
                exit;
            }

			try {
		        $customer = $this->objectManager->create('Magento\Customer\Model\Customer')->load($quote->getCustomerId());
		    } catch (\Exception $e) {
		        echo json_encode(['success' => 'false', 'msg' => 'Customer does not exist.']);
		        exit;
		    }
		    
            $quote->getShippingAddress()->setCollectShippingRates(true);

            $message = '';
            if (!empty($coupon_code)) {
                try {
                    $quote->setCouponCode($coupon_code);
                    $quote->collectTotals()->save();
                } catch (\Exception $e) {
                    echo json_encode(array('success' => 'false', 'msg' => $e->getMessage() . __('Could not apply coupon code.')));
                    exit;
                }
                if ($quote->getCouponCode() != $coupon_code) {
                    echo json_encode(array('success' => 'false', 'msg' => __('Coupon code is not valid.')));
                    exit;
                }
                $message = 'Coupon Code Applied Successfully';
            } else {
                try {
                    $quote->setCouponCode('');
                    $quote->collectTotals()->save();
//                    $this->quoteRepository->save($quote->collectTotals());
                } catch (\Exception $e) {
                    echo json_encode(array('success' => 'false', 'msg' => __('Could not delete coupon code.')));
                }
                if ($quote->getCouponCode() != '') {
                    echo json_encode(array('success' => 'false', 'msg' => __('Could not delete coupon code.')));
                }
                $message = 'Coupon Code Removed Successfully';
            }

            $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load($quote_id);
            $quote_items = $quote->getAllVisibleItems();
            
            $billingAddress = $quote->getBillingAddress();
            $shippingAddress = $quote->getShippingAddress();

            $billing_address = $billingAddress->getData();
            $shipping_address = $shippingAddress->getData();
            
            $billing_address = [
                'firstname' => (isset($billing_address['firstname']) ? $billing_address['firstname'] : ''),
                'lastname' => (isset($billing_address['lastname']) ? $billing_address['lastname'] : ''),
                'street' => (isset($billing_address['street']) ? $billing_address['street'] : ''),
                'city' => (isset($billing_address['city']) ? $billing_address['city'] : ''),
                'country_id' => (isset($billing_address['country_id']) ? $billing_address['country_id'] : ''),
                'region' => (isset($billing_address['region']) ? $billing_address['region'] : ''),
                'region_id' => (isset($billing_address['region']) ? $billing_address['region_id'] : ''),
                'zone' => (isset($billing_address['zone']) ? $billing_address['zone'] : ''),
                'building_number' => (isset($billing_address['building_number']) ? $billing_address['building_number'] : ''),
                'landmark' => (isset($billing_address['landmark']) ? $billing_address['landmark'] : ''),
                'telephone' => (isset($billing_address['telephone']) ? $billing_address['telephone'] : ''),
                'postcode' => (isset($billing_address['postcode']) ? $billing_address['postcode'] : ''),
                'customer_address_id' => $billingAddress->getData('customer_address_id')
            ];

            $shipping_address = [
                'firstname' => (isset($shipping_address['firstname']) ? $shipping_address['firstname'] : ''), //address Details
                'lastname' => (isset($shipping_address['lastname']) ? $shipping_address['lastname'] : ''),
                'street' => (isset($shipping_address['street']) ? $shipping_address['street'] : ''),
                'city' => (isset($shipping_address['city']) ? $shipping_address['city'] : ''),
                'country_id' => (isset($shipping_address['country_id']) ? $shipping_address['country_id'] : ''),
                'region' => (isset($shipping_address['region']) ? $shipping_address['region'] : ''),
                'region_id' => (isset($shipping_address['region_id']) ? $shipping_address['region_id'] : ''),
                'zone' => (isset($billing_address['zone']) ? $billing_address['zone'] : ''),
                'building_number' => (isset($billing_address['building_number']) ? $billing_address['building_number'] : ''),
                'landmark' => (isset($billing_address['landmark']) ? $billing_address['landmark'] : ''),
                'postcode' => (isset($shipping_address['postcode']) ? $shipping_address['postcode'] : ''),
                'telephone' => (isset($shipping_address['telephone']) ? $shipping_address['telephone'] : ''),
                'customer_address_id' => $shippingAddress->getData('customer_address_id')
            ];

            $method = $quote->getShippingAddress()->getShippingMethod();

            $shipping_rate = 0;
            if (!empty($method)) {
                $rate = $quote->getShippingAddress()->getShippingRateByCode($method);
                try {
                    $shipping_rate = (!empty($rate)) ? $rate->getPrice() : 0;
                } catch (\Exception $e) {
                    $shipping_rate = 0;
                }
            }

            $tax = $quote->getShippingAddress()->getTaxAmount();

            $discount_amount = $quote->getBaseSubtotal() - $quote->getBaseSubtotalWithDiscount();

            // Check for coupon code in quote
            $coupon_code = $quote->getCouponCode();
            $is_coupon_applied = 0;
            if (!empty($coupon_code)) {
                $is_coupon_applied = 1;
            }

            $data = array('success' => 'true', 'msg' => $message);
            
            $paymentMethods = $this->objectManager->get('\Magento\Quote\Api\PaymentMethodManagementInterface')->getList($quote->getId());
            $data['payment_methods'] = [];
            $grand_total = number_format($quote->getGrandTotal(), 4);

            $scopeConfig = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
            if (!empty($paymentMethods)) {
                foreach ($paymentMethods as $row) {
                    if ($grand_total == 0 && $row->getCode() != 'free')
                        continue;

                    $d = [
                        'title' => $row->getTitle(),
                        'code' => $row->getCode()
                    ];
                    array_push($data['payment_methods'], $d);
                }
            }

            $data['quoteId'] = $quote_id;
            $data['items'] = $this->helper->cartItemsFormat($quote_items);
            $data['billing_address'] = count($billing_address) ? $billing_address : array();
            $data['shipping_address'] = count($shipping_address) ? $shipping_address : array();
            $data['shipping_method_code'] = $method;
            
            $shipping_methods = $this->getShippingMethod($quote);
            $data['shipping_methods'] = $shipping_methods;

			$balance = $this->objectManager->create('Mirasvit\Rewards\Helper\Balance');
            $data['cart_points'] = $this->cartRewardPoints($quote);
			$data['user_points'] = $balance->getBalancePoints($customer);
			
			$data['points'] = $this->pointsData($quote, $customer);
			
			$priceHelper = $this->objectManager->get('Magento\Framework\Pricing\Helper\Data');
			
			$data['applied_donation'] = new \Magento\Framework\DataObject();

			$quote_donation = $quote->getShippingAddress()->getMageworxDonationDetails();
			if(!empty($quote_donation)){
				$donationDetails = $this->serializer->unserialize($quote_donation);
				if(isset($donationDetails['donation'])){
					$donation_value = isset($donationDetails['donation']) ? $donationDetails['donation'] : 0;
					$data['applied_donation'] = [];
					$data['applied_donation'] = [
						"donation" => (string) $donation_value,
						"donation_label" => (string) "+".$priceHelper->currency($donation_value, true, false),
						"charity_id" => "1",
						"charity_title" => "Education Above All"
					];
				}
			}
			$discount_without_currency = $quote->getGrandTotal() - $quote->getSubtotal();

            $data['summary'] = [
                'total' => $priceHelper->currency($quote->getBaseGrandTotal(), true, false),
                'sub_total' => $priceHelper->currency($quote->getBaseSubtotal(), true, false),
                'discount_amount' => "-".$priceHelper->currency($discount_amount, true, false),
                'total_without_currency' => str_replace(",", "", number_format($quote->getGrandTotal(),2)),
                'sub_total_without_currency' => str_replace(",", "", number_format($quote->getSubtotal(),2)),
                'discount_amount_without_currency' => str_replace(",", "", number_format($discount_without_currency,2)),
				'subtotal_with_discount' => str_replace(",", "", number_format($quote->getSubtotalWithDiscount(),2)),
                'is_coupon_added' => $is_coupon_applied,
                'coupon_code' => (empty($coupon_code) ? '' : $coupon_code),
                'shipping_amount' => "+".$priceHelper->currency($shipping_rate, true, false),
                'tax' => (string) $priceHelper->currency($tax, true, false)
            ];

            echo json_encode(array('success' => 'true', 'msg' => $message, 'collection' => $data));
            exit;
        } catch (\Exception $e) {
            echo json_encode(array('success' => 'false', 'msg' => $e->getMessage()));
            exit;
        }
    }
    
    protected function cartRewardPoints($quote){
		$this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		
		$purchaseHelper = $this->objectManager->get('\Mirasvit\Rewards\Helper\Purchase');
		$rewardsData = $this->objectManager->get('\Mirasvit\Rewards\Helper\Data');
		
		$purchase = $purchaseHelper->getByQuote($quote->getId());
		
		$earn_points_text = '';
		$earn_points = 0;
		if ($purchase) {
            $quote = $purchase->getQuote();
            
            if (strtotime($quote->getUpdatedAt()) < (time() - $purchase->getRefreshPointsTime())) {
                $purchase->updatePoints();
                $purchase = $purchaseHelper->getByQuote($quote->getId()); // load updated purchase
            }
            
            $earn_points = $purchase->getEarnPints();
            if(empty($earn_points)){
            	$earn_points = $purchase->getData('earn_points');
            }
            if ($earn_points) {
                $earn_points_text = $rewardsData->formatPoints($earn_points);
            }
        }

        return ['cart_points' => $earn_points, 'cart_points_label' => $earn_points_text];
    }
    
    /**
     * Redeem points
	 *
     * @return array
     */
    public function redeemPoints() {
    	$postData = file_get_contents("php://input");
        if (!empty($postData)) {
            $request = json_decode($postData, true);
        }

        $quote_id = $request['quote_id'];
        $points = $request['points'];
        $remove_points = $request['remove_points'];
        
        $data = array();
        
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		//Multi store view
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
		$apiHelper = $this->objectManager->get('Yalla\Apis\Helper\Data');
        $apiHelper->setStore($lang, $country);
        //Multi store view
        
        try {
            try {
                $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load($quote_id);
            } catch (\Exception $e) {
                echo json_encode(array('success' => 'false', 'msg' => __("Cart does not exist!")));
                exit;
            }

            if (!$quote->getItemsCount()) {
                echo json_encode(array('success' => 'false', 'msg' => __("Cart does not contain products!")));
                exit;
            }

			try {
		        $customer = $this->objectManager->create('Magento\Customer\Model\Customer')->load($quote->getCustomerId());
		    } catch (\Exception $e) {
		        echo json_encode(['success' => 'false', 'msg' => 'Customer does not exist.']);
		        exit;
		    }
        
			$purchaseHelper = $this->objectManager->get('\Mirasvit\Rewards\Helper\Purchase');
			$rewardsData = $this->objectManager->get('\Mirasvit\Rewards\Helper\Data');
			$taxConfig = $this->objectManager->get('\Magento\Tax\Model\Config');
			
			$purchase = $purchaseHelper->getByQuote($quote->getId());

		    if (!$purchase->getQuote()->getIsVirtual() &&
		        empty(trim($purchase->getQuote()->getShippingAddress()->getShippingMethod(), '_'))
		    ) {
		    
		        if ($purchase->getQuote()->getShippingAddress()->getCountryId() === null) {
		        	$addressData = $quote->getShippingAddress()->getData();
		            //$addressData = (array)json_decode($this->getRequest()->getParam('address'), true);
		            $convertData = [
		                'customerAddressId' => 'customer_address_id',
		                'countryId'         => 'country_id',
		                'regionId'          => 'region_id',
		                'regionCode'        => 'region_code',
		                'customerId'        => 'customer_id',
		            ];
		            foreach ($addressData as $k => $v) {
		                if (isset($convertData[$k])) {
		                    $purchase->getQuote()->getShippingAddress()->setData($convertData[$k], $v);
		                } else {
		                    $purchase->getQuote()->getShippingAddress()->setData($k, $v);
		                }
		            }
		        }
		        $shippingCarrier = 'flat_rate';
		        $shippingMethod = 'flat_rate';
		        
		        $purchase->getQuote()->setCartShippingCarrier($shippingCarrier);
		        $purchase->getQuote()->setCartShippingMethod($shippingMethod);
		        $purchase->getQuote()->getShippingAddress()
		            ->setCollectShippingRates(true)
		            ->setShippingMethod(
		            $shippingCarrier . '_' . $shippingMethod
		        );
		    }
		    
		    $pointsNumber = abs((int) $points);
		    
		    if ($remove_points == 1) {
		        $pointsNumber = 0;
		    }
		    
		    $oldPointsNumber = $purchase->getSpendPoints();

		    if ($pointsNumber <= 0 && $oldPointsNumber <= 0) {
		        echo json_encode(['success' => 'false', 'msg' => "You don't have enough points."]);
		        exit;
		    }

		    try {
		        //$this->updatePurchase($purchase, $pointsNumber);
		        // Update
		        $purchase
				    ->setSaveItemIds(true)
				    ->setSpendPoints($pointsNumber);
				if (!$pointsNumber) {
				    $purchase->setBaseSpendAmount(0)
				        ->setSpendAmount(0);
				}
				$purchase->save();

				$quote = $purchase->getQuote();
				if ($taxConfig->applyTaxAfterDiscount()) {
				    $purchase->updatePoints(); // apply rewards discount
				    $quote->setTotalsCollectedFlag(false); // recalculate tax with rewards discount
				}
				$quote->collectTotals()->save();
		        
		        if ($pointsNumber) {
				    $data['success'] = 'true';
				    $data['msg'] = __(
				        '%1 were applied.', $rewardsData->formatPoints($purchase->getSpendPoints())
				    );
				    if($purchase->getSpendPoints()){
				    	$data['msg'] = $message = __("Reward Points Applied Successfully");
				    }else{
				    	$message = $data['msg'];
				    }
				    
				    // do not check max because max will be use instead of $pointsNumber
				    if ($pointsNumber != $purchase->getSpendPoints() && $pointsNumber < $purchase->getSpendMinPoints()) {
				        $data['success'] = 'false';
				        $data['msg'] = __(
				            'Minimum number is %1.', $rewardsData->formatPoints($purchase->getSpendMinPoints())
				        );
				        $message = __(
				            'Minimum number is %1.', $rewardsData->formatPoints($purchase->getSpendMinPoints())
				        );
				    }
				} else {
				    $data['success'] = 'true';
				    $data['msg'] = __('%1 were cancelled.', $rewardsData->getPointsName());
				    $data['msg'] = $message = __("Reward Points Removed Successfully");
				}
		    } catch (\Exception $e) {
		    	echo $e->getMessage();
		        $data['success'] = 'false';
		        $data['msg'] = __('Cannot apply %1.', $rewardsData->getPointsName());
		        $message = __('Cannot apply %1.', $rewardsData->getPointsName());
		    }
		    $data['spend_points'] = $purchase->getSpendPoints();
		    $data['spend_points_formated'] = $rewardsData->formatPoints($purchase->getSpendPoints());

			
            $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load($quote_id);
            $quote_items = $quote->getAllVisibleItems();
            
            $billingAddress = $quote->getBillingAddress();
            $shippingAddress = $quote->getShippingAddress();

            $billing_address = $billingAddress->getData();
            $shipping_address = $shippingAddress->getData();
            
            $billing_address = [
                'firstname' => (isset($billing_address['firstname']) ? $billing_address['firstname'] : ''),
                'lastname' => (isset($billing_address['lastname']) ? $billing_address['lastname'] : ''),
                'street' => (isset($billing_address['street']) ? $billing_address['street'] : ''),
                'city' => (isset($billing_address['city']) ? $billing_address['city'] : ''),
                'country_id' => (isset($billing_address['country_id']) ? $billing_address['country_id'] : ''),
                'region' => (isset($billing_address['region']) ? $billing_address['region'] : ''),
                'region_id' => (isset($billing_address['region']) ? $billing_address['region_id'] : ''),
                'zone' => (isset($billing_address['zone']) ? $billing_address['zone'] : ''),
                'building_number' => (isset($billing_address['building_number']) ? $billing_address['building_number'] : ''),
                'landmark' => (isset($billing_address['landmark']) ? $billing_address['landmark'] : ''),
                'telephone' => (isset($billing_address['telephone']) ? $billing_address['telephone'] : ''),
                'postcode' => (isset($billing_address['postcode']) ? $billing_address['postcode'] : ''),
                'customer_address_id' => $billingAddress->getData('customer_address_id')
            ];

            $shipping_address = [
                'firstname' => (isset($shipping_address['firstname']) ? $shipping_address['firstname'] : ''), //address Details
                'lastname' => (isset($shipping_address['lastname']) ? $shipping_address['lastname'] : ''),
                'street' => (isset($shipping_address['street']) ? $shipping_address['street'] : ''),
                'city' => (isset($shipping_address['city']) ? $shipping_address['city'] : ''),
                'country_id' => (isset($shipping_address['country_id']) ? $shipping_address['country_id'] : ''),
                'region' => (isset($shipping_address['region']) ? $shipping_address['region'] : ''),
                'region_id' => (isset($shipping_address['region_id']) ? $shipping_address['region_id'] : ''),
                'zone' => (isset($billing_address['zone']) ? $billing_address['zone'] : ''),
                'building_number' => (isset($billing_address['building_number']) ? $billing_address['building_number'] : ''),
                'landmark' => (isset($billing_address['landmark']) ? $billing_address['landmark'] : ''),
                'postcode' => (isset($shipping_address['postcode']) ? $shipping_address['postcode'] : ''),
                'telephone' => (isset($shipping_address['telephone']) ? $shipping_address['telephone'] : ''),
                'customer_address_id' => $shippingAddress->getData('customer_address_id')
            ];

            $method = $quote->getShippingAddress()->getShippingMethod();

            $shipping_rate = 0;
            if (!empty($method)) {
                $rate = $quote->getShippingAddress()->getShippingRateByCode($method);
                try {
                    $shipping_rate = (!empty($rate)) ? $rate->getPrice() : 0;
                } catch (\Exception $e) {
                    $shipping_rate = 0;
                }
            }

            $tax = $quote->getShippingAddress()->getTaxAmount();

            $discount_amount = $quote->getBaseSubtotal() - $quote->getBaseSubtotalWithDiscount();

            // Check for coupon code in quote
            $coupon_code = $quote->getCouponCode();
            $is_coupon_applied = 0;
            if (!empty($coupon_code)) {
                $is_coupon_applied = 1;
            }
            
            $paymentMethods = $this->objectManager->get('\Magento\Quote\Api\PaymentMethodManagementInterface')->getList($quote->getId());
            $data['payment_methods'] = [];
            $grand_total = number_format($quote->getGrandTotal(), 4);

            $scopeConfig = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
            if (!empty($paymentMethods)) {
                foreach ($paymentMethods as $row) {
                    if ($grand_total == 0 && $row->getCode() != 'free')
                        continue;

                    $d = [
                        'title' => $row->getTitle(),
                        'code' => $row->getCode()
                    ];
                    array_push($data['payment_methods'], $d);
                }
            }

            $data['quoteId'] = $quote_id;
            $data['items'] = $this->helper->cartItemsFormat($quote_items);
            $data['billing_address'] = count($billing_address) ? $billing_address : array();
            $data['shipping_address'] = count($shipping_address) ? $shipping_address : array();
            $data['shipping_method_code'] = $method;

			$shipping_methods = $this->getShippingMethod($quote);
			$data['shipping_methods'] = $shipping_methods;

			$balance = $this->objectManager->create('Mirasvit\Rewards\Helper\Balance');
            $data['cart_points'] = $this->cartRewardPoints($quote);
			$data['user_points'] = $balance->getBalancePoints($customer);
			
			$data['points'] = $this->pointsData($quote, $customer);
			
			$priceHelper = $this->objectManager->get('Magento\Framework\Pricing\Helper\Data');
			
			$data['applied_donation'] = new \Magento\Framework\DataObject();

			$quote_donation = $quote->getShippingAddress()->getMageworxDonationDetails();
			if(!empty($quote_donation)){
				$donationDetails = $this->serializer->unserialize($quote_donation);
				if(isset($donationDetails['donation'])){
					$donation_value = isset($donationDetails['donation']) ? $donationDetails['donation'] : 0;
					$data['applied_donation'] = [];
					$data['applied_donation'] = [
						"donation" => (string) $donation_value,
						"donation_label" => (string) "+".$priceHelper->currency($donation_value, true, false),
						"charity_id" => "1",
						"charity_title" => "Education Above All"
					];
				}
			}

            $data['summary'] = [
                'total' => $priceHelper->currency($quote->getBaseGrandTotal(), true, false),
                'sub_total' => $priceHelper->currency($quote->getBaseSubtotal(), true, false),
                'discount_amount' => "-".$priceHelper->currency($discount_amount, true, false),
                'total_without_currency' => str_replace("," , "", number_format($quote->getGrandTotal(),2)),
                'sub_total_without_currency' => str_replace("," , "", number_format($quote->getSubtotal(),2)),
                'subtotal_with_discount' => str_replace("," , "", number_format($quote->getSubtotalWithDiscount(),2)),
                'discount_amount_without_currency' => str_replace("," , "", number_format($discount_amount,2)),
                'is_coupon_added' => $is_coupon_applied,
                'coupon_code' => (empty($coupon_code) ? '' : $coupon_code),
                'shipping_amount' => "+".$priceHelper->currency($shipping_rate, true, false),
                'tax' => (string) $priceHelper->currency($tax, true, false)
            ];

            echo json_encode(array('success' => 'true', 'msg' => $message, 'collection' => $data));
            exit;
        } catch (\Exception $e) {
            echo json_encode(array('success' => 'false', 'msg' => $e->getMessage()));
            exit;
        }
    }
    
    public function pointsData($quote, $customer){
    	$grand_total = $quote->getGrandTotal();
    	
    	$purchaseHelper = $this->objectManager->get('\Mirasvit\Rewards\Helper\Purchase');
        $purchase = $purchaseHelper->getByQuote($quote->getId());
        $used_points = (int) $purchase->getSpendPoints();
        $used_points_value = !empty($purchase->getSpendAmount()) ? $purchase->getSpendAmount() : 0;
            
    	$pointsBalance = $this->objectManager->create('Mirasvit\Rewards\Helper\Balance');
    	$total_balance = $pointsBalance->getBalancePoints($customer);
    	
		$total_balance_value = 0;
		$usable_points = 0;
		$usable_points_value = 0;
		
		$calculatePoints = $this->objectManager->create('Mirasvit\Rewards\Helper\Balance\CalculatePoints');
		$current_cart_points = $calculatePoints->getCartPoints($quote, $total_balance, null);

		$minimum_points_required = 0;
		if(isset($current_cart_points['points'])){
			if($current_cart_points['amount']){
				$minimum_points_required = $current_cart_points['points']/$current_cart_points['amount'];
			}
			if($current_cart_points['points'] < $total_balance && $minimum_points_required){
				$total_balance_value = $total_balance/$minimum_points_required;
			}else{
				$total_balance_value = $current_cart_points['amount'];
			}
			if($total_balance_value >= 1 && $grand_total >= 1){
				if($current_cart_points['amount'] > $grand_total){
					$usable_points = $grand_total*$minimum_points_required;
					$usable_points_value = $grand_total;
				}else{
					$usable_points = $current_cart_points['points'];
					$usable_points_value = $current_cart_points['amount'];
				}
			}
		}

		$remaining_points = 0;
		if($total_balance){
			$remaining_points = $total_balance - $used_points;
		}
		
		if($minimum_points_required > $remaining_points && !$used_points){
			$usable_points = 0;
			$usable_points_value = 0;
		}
		
		if($used_points && $remaining_points && $usable_points){
			$usable_points = $usable_points - $used_points;
			$usable_points_value = $usable_points_value - $used_points_value;
		}
		$priceHelper = $this->objectManager->get('Magento\Framework\Pricing\Helper\Data');
		
		return [
			'min_points' => "100",
			'spend_points' => (string) $used_points,
			'spend_points_amount' => "-".$priceHelper->currency($used_points_value, true, false),
			'remaining_points' => $remaining_points,
			'total_balance' => (string) $total_balance,
			//'total_balance_value' => (string) $total_balance_value,
			'usable_points' => (string) $usable_points,
			//'usable_points_value' => (string) $usable_points_value,
		];
    }
    
    public function reorder(){
		
		$api_auth = $this->objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
    	$postData = file_get_contents("php://input");
    	$request = [];
    	if (!empty($postData)) {
            $request = json_decode($postData, true);
        }
        if (!count($request)) {
        	echo json_encode(array('success' => 'false', 'msg' => 'Mandatory parameter is missing.'));
		    exit;
        }
    	
		try{

			if (!isset($request['customer_id'])) {
			    echo json_encode(array('success' => 'false', 'msg' => 'Mandatory parameter is missing.'));
			    exit;
			}
			if (!isset($request['order_number'])) {
			    echo json_encode(array('success' => 'false', 'msg' => 'Mandatory parameter is missing.'));
			    exit;
			}

			$customerId = $request['customer_id'];
			
			try {
			    $customer = $this->objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
			} catch (\Exception $e) {
			    echo json_encode(['success' => 'false', 'msg' => 'Customer does not exist.']);
			    exit;
			}

			$order_number = $request['order_number'];

			$store_id = 1;
			//Multi store view
		    $lang = $this->request->getParam('lang');
			$country = $this->request->getParam('country');
			$apiHelper = $this->objectManager->get('Yalla\Apis\Helper\Data');
		    $apiHelper->setStore($lang, $country);
		    //Multi store view

			$store = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface');
			$store = $store->getStore();

			$order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($order_number);
			$order_number = $order->getIncrementId();

			if(empty($order_number)){
			    echo json_encode(['success' => 'false', 'msg' => 'Order not found!']);
			    exit;
			}

			// Get active quotes
			$cartRepository = $this->objectManager->get('\Magento\Quote\Api\CartRepositoryInterface');
			$quote = $this->quoteFactory->create()->getCollection()->addFieldToFilter('is_active', true)
					->addFieldToFilter('customer_id', $customerId);

			$quoteData = $quote->getData();
			if (isset($quoteData[0]['entity_id'])) {
			    $quote = $cartRepository->get($quoteData[0]['entity_id']);
			}

			// Create blank cart if customer don't have any active cart
			if (!count($quoteData)) {
		        $customer = $this->objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface')->getById($customerId);
			    $cartId = $this->cartManagementInterface->createEmptyCart(); //Create empty cart
			    $quote = $this->cartRepositoryInterface->get($cartId); // load emptycartManagementInterface cart quote
			    $quote->setStore($store);
			    $quote->setCurrency();
			    
			    //Assign quote to customer
			    $quote->assignCustomer($customer);
			}

			$items = $order->getAllVisibleItems();
			foreach ($items as $item) {
				$info = $item->getProductOptionByCode('info_buyRequest');
				$info = new \Magento\Framework\DataObject($info);
				$_product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());

				$quote->addProduct($_product, $info);
				$quote->save();
				$quote->collectTotals()->save();
			}

			echo json_encode(['success' => 'true', 'msg' => 'Cart updated.']);
		    exit;

		}catch(\Exception $ex){
			echo json_encode(['success' => 'false', 'msg' => $ex->getMessage()]);
		    exit;
		}
    }
    
    /**
     * Redeem points
	 *
     * @return array
     */
    public function applyDonation() {
    
    	//Multi store view
		$api_auth = $this->objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
		$lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
		$apiHelper = $this->objectManager->get('Yalla\Apis\Helper\Data');
		$apiHelper->setStore($lang, $country);
        $currency = $this->request->getParam('currency');
        if($currency) {
        	$this->_storeManager->getStore()->setCurrentCurrencyCode($currency);
        }
		//Multi store view
		
    	$postData = file_get_contents("php://input");
        if (!empty($postData)) {
            $request = json_decode($postData, true);
        }

        $quote_id = $request['quote_id'];
        $donation = $request['donation'];
        $remove = $request['remove'];
        
        $data = array();
		$message = 'Success';
        try {
            try {
                $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load($quote_id);
            } catch (\Exception $e) {
                echo json_encode(array('success' => 'false', 'msg' => __("Cart does not exist!")));
                exit;
            }

            if (!$quote->getItemsCount()) {
                echo json_encode(array('success' => 'false', 'msg' => __("Cart does not contain products!")));
                exit;
            }

			try {
		        $customer = $this->objectManager->create('Magento\Customer\Model\Customer')->load($quote->getCustomerId());
		    } catch (\Exception $e) {
		        echo json_encode(['success' => 'false', 'msg' => 'Customer does not exist.']);
		        exit;
		    }
        
			// Assign donation
            if(isset($request['donation']) && $request['donation'] > 0){
            	$remove = 0; 
            	if(isset($request['remove'])){
            		$remove = $request['remove'];
            	}
            	$donationHelper = $this->objectManager->create('Yalla\Apis\Helper\Donation');
            	$donation_result = $donationHelper->applyDonation($quote, $request['donation'], $remove);
            	if($donation_result['result'] === 'true' && $remove == 1){
            		$message = __("Donation has been removed!");
            	} else if($donation_result['result'] === 'true'){
            		$message = __("Thank you for your donation!");
            	} else if(isset($donation_result['error'])){
            		$message = $donation_result['error'];
            	}
            	/*$serializer = $this->objectManager->create('\Magento\Framework\Serialize\SerializerInterface');
            	$address = $quote->getShippingAddress();
            	$address->setMageworxDonationDetails($serializer->serialize(array(
            		"global_donation" => $request['donation'],
            		"donation" => $request['donation'],
            		"donation_roundup" => 0,
            		"isUseDonationRoundUp" => false,
            		"charity_id" => "1",
            		"charity_title" => "Education Above All"
            	)));
            	$address->save();
            	$quote->collectTotals()->save();*/
            }
            // Assign donation
			
            $quote_items = $quote->getAllVisibleItems();
            
            $billingAddress = $quote->getBillingAddress();
            $shippingAddress = $quote->getShippingAddress();

            $billing_address = $billingAddress->getData();
            $shipping_address = $shippingAddress->getData();
            
            $billing_address = [
                'firstname' => (isset($billing_address['firstname']) ? $billing_address['firstname'] : ''),
                'lastname' => (isset($billing_address['lastname']) ? $billing_address['lastname'] : ''),
                'street' => (isset($billing_address['street']) ? $billing_address['street'] : ''),
                'city' => (isset($billing_address['city']) ? $billing_address['city'] : ''),
                'country_id' => (isset($billing_address['country_id']) ? $billing_address['country_id'] : ''),
                'region' => (isset($billing_address['region']) ? $billing_address['region'] : ''),
                'region_id' => (isset($billing_address['region']) ? $billing_address['region_id'] : ''),
                'zone' => (isset($billing_address['zone']) ? $billing_address['zone'] : ''),
                'building_number' => (isset($billing_address['building_number']) ? $billing_address['building_number'] : ''),
                'landmark' => (isset($billing_address['landmark']) ? $billing_address['landmark'] : ''),
                'telephone' => (isset($billing_address['telephone']) ? $billing_address['telephone'] : ''),
                'postcode' => (isset($billing_address['postcode']) ? $billing_address['postcode'] : ''),
                'customer_address_id' => $billingAddress->getData('customer_address_id')
            ];

            $shipping_address = [
                'firstname' => (isset($shipping_address['firstname']) ? $shipping_address['firstname'] : ''), //address Details
                'lastname' => (isset($shipping_address['lastname']) ? $shipping_address['lastname'] : ''),
                'street' => (isset($shipping_address['street']) ? $shipping_address['street'] : ''),
                'city' => (isset($shipping_address['city']) ? $shipping_address['city'] : ''),
                'country_id' => (isset($shipping_address['country_id']) ? $shipping_address['country_id'] : ''),
                'region' => (isset($shipping_address['region']) ? $shipping_address['region'] : ''),
                'region_id' => (isset($shipping_address['region_id']) ? $shipping_address['region_id'] : ''),
                'zone' => (isset($billing_address['zone']) ? $billing_address['zone'] : ''),
                'building_number' => (isset($billing_address['building_number']) ? $billing_address['building_number'] : ''),
                'landmark' => (isset($billing_address['landmark']) ? $billing_address['landmark'] : ''),
                'postcode' => (isset($shipping_address['postcode']) ? $shipping_address['postcode'] : ''),
                'telephone' => (isset($shipping_address['telephone']) ? $shipping_address['telephone'] : ''),
                'customer_address_id' => $shippingAddress->getData('customer_address_id')
            ];

            $method = $quote->getShippingAddress()->getShippingMethod();

            $shipping_rate = 0;
            if (!empty($method)) {
                $rate = $quote->getShippingAddress()->getShippingRateByCode($method);
                try {
                    $shipping_rate = (!empty($rate)) ? $rate->getPrice() : 0;
                } catch (\Exception $e) {
                    $shipping_rate = 0;
                }
            }

            $tax = $quote->getShippingAddress()->getTaxAmount();

            $discount_amount = $quote->getBaseSubtotal() - $quote->getBaseSubtotalWithDiscount();

            // Check for coupon code in quote
            $coupon_code = $quote->getCouponCode();
            $is_coupon_applied = 0;
            if (!empty($coupon_code)) {
                $is_coupon_applied = 1;
            }
            
            $paymentMethods = $this->objectManager->get('\Magento\Quote\Api\PaymentMethodManagementInterface')->getList($quote->getId());
            $data['payment_methods'] = [];
            $grand_total = number_format($quote->getGrandTotal(), 4);

            $scopeConfig = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
            if (!empty($paymentMethods)) {
                foreach ($paymentMethods as $row) {
                    if ($grand_total == 0 && $row->getCode() != 'free')
                        continue;

                    $d = [
                        'title' => $row->getTitle(),
                        'code' => $row->getCode()
                    ];
                    array_push($data['payment_methods'], $d);
                }
            }

            $data['quoteId'] = $quote_id;
            $data['items'] = $this->helper->cartItemsFormat($quote_items);
            $data['billing_address'] = count($billing_address) ? $billing_address : array();
            $data['shipping_address'] = count($shipping_address) ? $shipping_address : array();
            $data['shipping_method_code'] = $method;

			$shipping = $this->calculateShipping($quote, $method);
            $method = $shipping['method'];
            $shipping_rate = $shipping['rate'];
            $quote = $shipping['updated_quote'];
            
			$shipping_methods = $this->getShippingMethod($quote);
			$data['shipping_methods'] = $shipping_methods;

			$balance = $this->objectManager->create('Mirasvit\Rewards\Helper\Balance');
            $data['cart_points'] = $this->cartRewardPoints($quote);
			$data['user_points'] = $balance->getBalancePoints($customer);
			
			$data['points'] = $this->pointsData($quote, $customer);

			$priceHelper = $this->objectManager->get('Magento\Framework\Pricing\Helper\Data');

			$data['applied_donation'] = new \Magento\Framework\DataObject();

			$quote_donation = $quote->getShippingAddress()->getMageworxDonationDetails();
			if(!empty($quote_donation)){
				$donationDetails = $this->serializer->unserialize($quote_donation);
				if(isset($donationDetails['donation'])){
					$donation_value = isset($donationDetails['donation']) ? $donationDetails['donation'] : 0;
					$data['applied_donation'] = [];
					$data['applied_donation'] = [
						"donation" => (string) $donation_value,
						"donation_label" => (string) "+".$priceHelper->currency($donation_value, true, false),
						"charity_id" => "1",
						"charity_title" => "Education Above All"
					];
				}
			}

            $data['summary'] = [
                'total' => $priceHelper->currency($quote->getBaseGrandTotal(), true, false),
                'sub_total' => $priceHelper->currency($quote->getBaseSubtotal(), true, false),
                'discount_amount' => "-".$priceHelper->currency($discount_amount, true, false),
                'total_without_currency' => str_replace(",", "", number_format($quote->getGrandTotal(),2)),
				'sub_total_without_currency' => str_replace(",", "", number_format($quote->getSubtotal(),2)),
				'subtotal_with_discount' => str_replace(",", "", number_format($quote->getSubtotalWithDiscount(),2)),
				'discount_amount_without_currency' => str_replace(",", "", number_format($discount_amount,2)),
                'is_coupon_added' => $is_coupon_applied,
                'coupon_code' => (empty($coupon_code) ? '' : $coupon_code),
                'shipping_amount' => "+".$priceHelper->currency($shipping_rate, true, false),
                'tax' => (string) $priceHelper->currency($tax, true, false)
            ];

            echo json_encode(array('success' => 'true', 'msg' => $message, 'collection' => $data));
            exit;
        } catch (\Exception $e) {
            echo json_encode(array('success' => 'false', 'msg' => $e->getMessage()));
            exit;
        }
    }

}
