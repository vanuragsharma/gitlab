<?php

namespace Yalla\Apis\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Order implements \Yalla\Apis\Api\OrderInterface
{
    protected $storeManager;

	protected $customerRepository;

	protected $request;
	
	protected $objectManager;
	
	protected $helper;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
    	StoreManagerInterface $storeManager,
    	CustomerRepositoryInterface $customerRepository,
		\Magento\Framework\App\Request\Http $request
    ) {
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
		$this->request = $request;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $this->objectManager->get('Yalla\Apis\Helper\Data');
    }
    
    /**
     * Returns customer's orders
     *
     * @return array
     */
    public function getOrders()
    {
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
        
        //Multi store view
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
        $this->helper->setStore($lang, $country);
        $currency = $this->request->getParam('currency');
        if($currency) {
        	$this->storeManager->getStore()->setCurrentCurrencyCode($currency);
        }
        //Multi store view

        if (empty($request['customer_id'])) {
            echo json_encode(['success' => 'false', 'msg' => __('Invalid parameters!')]);
            exit;
        }
        $customerId = $request['customer_id'];
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => __('Customer does not exist!')]);
            exit;
        }
        
        $priceHelper = $this->objectManager->get('Magento\Framework\Pricing\Helper\Data');
        
        if(isset($request['order']) && !empty($request['order'])){
        	$order = $this->objectManager->create('\Magento\Sales\Model\Order')->loadByIncrementId($request['order']);

            if ($order->getId()) {

				if($order->getCustomerEmail() != $customer->getEmail()){
					echo json_encode(['success' => 'false', 'msg' => __('Order does not exist!')]);
            		exit;
				}
				
				$list = array();
				$list['order_number'] = $order->getData('increment_id');
                
                $items = $order->getAllVisibleItems();
                $orderItems = array();
				$imageHelper = $this->objectManager->get('\Magento\Catalog\Helper\Image');

                foreach ($items as $item) {
                    $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());

					$imageUrl = $imageHelper->getDefaultPlaceholderUrl('image');
					
					$config_options = $item->getProductOptions();
					$configurable_options = [];
					
					if (isset($config_options['attributes_info']) && !empty($config_options['attributes_info'])) {        
						foreach ($config_options['attributes_info'] as $option) {
							$configurable_options[] = [
								'attribute_id' => $option['option_id'],
								'type' => $option['label'],
								'attribute_code' => $option['label'],
								'attributes' => [
									"value" => $option['value'],
								    "option_id" => $option['option_value'],
								    "price" => ""
								]
							];
						}
					}
					
					$product_id = $this->objectManager->create('Magento\Catalog\Model\Product')->getIdBySku($item->getSku());
					if($product_id) {
						$_child_product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
						$imageUrl = $imageHelper->getDefaultPlaceholderUrl('image');
						if($_child_product->getData('image')){
							$imageUrl = $imageHelper->init($_child_product, 'category_page_list')->setImageFile($_child_product->getData('image'))->resize(300,300)->getUrl();
						}
					}elseif($product->getData('image')) {
						$imageUrl = $imageHelper->init($product, 'category_page_list')->setImageFile($product->getData('image'))->resize(300,300)->getUrl();
					}
					
					$options = [];
                    $orderItems[] = array(
                        'item_id' => $item->getItemId(),
                        'product_id' => $item->getProductId(),
                        'type_id' => $item->getProductType(),
                        'image' => $imageUrl,
                        'sku' => $item->getSku(),
                        'qty' => (int) $item->getQtyOrdered(),
                        'qty_canceled' => (int) $item->getQtyCanceled(),
                        'qty_invoiced' => (int) $item->getQtyInvoiced(),
                        'qty_shipped' => (int) $item->getQtyShipped(),
                        'qty_refunded' => (int) $item->getQtyRefunded(),
                        'name' => $item->getName(),
                        'discount_amount' => $priceHelper->currency($item->getDiscountAmount(), true, false),
                        'unit_price' => $priceHelper->currency($item->getPrice(), true, false),
                        'total_price' => $priceHelper->currency($item->getRowTotal(), true, false),
                    	'configurable_option' => $configurable_options,
                    );
                }

                $list['items'] = $orderItems;
                
				$payment = $order->getPayment();
				$method = $payment->getMethodInstance();
				
				$order_timestamp = strtotime($order->getData('created_at'));
				
				$list['order_info'] = [
                	'payment_method' => $method->getTitle(),
                	'created_at' => date("d F\, Y", $order_timestamp),
                	'order_status' => $order->getStatusLabel(),
                	'order_status_color' => $this->orderStatusColor($order->getData('status')),
                	'shipping_method' => $order->getData('shipping_method')
                ];
				
				$list['applied_donation'] = new \Magento\Framework\DataObject();

				$serializer = $this->objectManager->create('Magento\Framework\Serialize\SerializerInterface');

				$quote_donation = $order->getMageworxDonationDetails();
				if(!empty($quote_donation)){
					$donationDetails = $serializer->unserialize($quote_donation);
					if(isset($donationDetails['donation'])){
						$donation_value = isset($donationDetails['donation']) ? $donationDetails['donation'] : 0;
						$list['applied_donation'] = [];
						$list['applied_donation'] = [
							"donation" => (string) $donation_value,
							"donation_label" => (string) "+".$priceHelper->currency($donation_value, true, false),
							"charity_id" => "1",
							"charity_title" => "Education Above All"
						];
					}
				}
			
				$coupon_code = $order->getCouponCode();
		        
		        $order_points = $this->getUsedPoints($order);
				$order['earned_points'] = $order_points['earned_points'];
				$order['earned_points_text'] = $order_points['earned_points_text'];

				$list['payment_details'] = [
                    'discount_amount' => (string) $priceHelper->currency($order->getData('discount_amount'), true, false),
                    'coupon_code' => (empty($coupon_code) ? '' : $coupon_code),
                    'tax' => $priceHelper->currency($order->getData('tax_amount'), true, false),
					'points_used' => $order_points['spend_points'],
		            'points_discount' => "-".$priceHelper->currency($order_points['spend_points_amount'], true, false),
                    'subtotal' => $priceHelper->currency($order->getData('subtotal'), true, false),
                    'shipping_amount' => "+".$priceHelper->currency($order->getData('shipping_amount'), true, false),
                    'grand_total' => $priceHelper->currency($order->getData('grand_total'), true, false),
                    'total_due' => $priceHelper->currency($order->getData('total_due'), true, false),
                    'total_paid' => $priceHelper->currency($order->getData('total_paid'), true, false),
                    'total_refunded' => $priceHelper->currency($order->getData('total_refunded'), true, false)
                ];
                
                $billingAddress = $order->getBillingAddress()->getData();
                $shippingAddress = [];
                if($order->getShippingAddress()){
                	$shippingAddress = $order->getShippingAddress()->getData();
                }
                $building_number = '';
                $addressRepository = $this->objectManager->create('\Magento\Customer\Api\AddressRepositoryInterface');
                try {
				    $customerAddressData = $addressRepository->getById($billingAddress['customer_address_id']);
				    $addressData = $customerAddressData->getStreet();
				    if(isset($addressData[1])){
				    	$building_number = $addressData[1];
				    }
				} catch (\Exception $exception) {
				    //throw new \Exception($exception->getMessage());
				}
                
                $list['billingAddress'] = [
                	'name' => $billingAddress['firstname']." ".$billingAddress['lastname'],
                	'phone_no' => $billingAddress['telephone'],
                	'building_number' => $building_number,
                	'street' => $billingAddress['street'],
                	'city' => $billingAddress['city'],
                	'state' => $billingAddress['region'],
                	'postcode' => !empty($billingAddress['postcode']) ? $billingAddress['postcode'] : '',
                	'country' => $billingAddress['country_id']
                ];
                
                try {
				    $customerAddressData = $addressRepository->getById($shippingAddress['customer_address_id']);
				    $addressData = $customerAddressData->getStreet();
				    if(isset($addressData[1])){
				    	$building_number = $addressData[1];
				    }
				} catch (\Exception $exception) {
				    //throw new \Exception($exception->getMessage());
				}
				
                if(count($shippingAddress)){
		            $list['shippingAddress'] = [
		            	'name' => $shippingAddress['firstname']." ".$shippingAddress['lastname'],
		            	'phone_no' => $shippingAddress['telephone'],
		            	'building_number' => $building_number,
		            	'street' => $shippingAddress['street'],
		            	'city' => $shippingAddress['city'],
		            	'state' => $shippingAddress['region'],
		            	'postcode' => !empty($shippingAddress['postcode']) ? $shippingAddress['postcode'] : '',
		            	'country' => $shippingAddress['country_id']
		            ];
                }else{
                	$list['shippingAddress'] = (object) [];
            	}
                
				echo json_encode(['success' => 'true', 'msg' => __('Success'), 'collection' => $list]);
        		exit;
			}
        }else{
        	$orders = $this->objectManager->create('\Magento\Sales\Model\Order')->getCollection();
            $orders->addAttributeToFilter('customer_id', $customerId)->addFieldToFilter('status', array('neq' => 'order_reversed'))
			->setOrder('entity_id','DESC');
			
			$lists = array();
			foreach($orders as $order){
				$list['order_number'] = $order->getData('increment_id');
                $list['order_status'] = $order->getStatusLabel();
                $list['order_status_color'] = $this->orderStatusColor($order->getData('status'));
                $list['order_total'] = $priceHelper->currency($order->getData('grand_total'), true, false);
                
                $order_timestamp = strtotime($order->getData('created_at'));
                $list['created_at'] = date("d F\, Y", $order_timestamp);
                
                $lists[] = $list;
			}
			
			echo json_encode(['success' => 'true', 'msg' => __('Success'), 'collection' => $lists]);
        	exit;
        }
        
        echo json_encode(['success' => 'true', 'msg' => __('Success'), 'collection' => []]);
        exit;
    }
    
    private function orderStatusColor($status){
    	$color = '#000000';
    	if($status == 'processing'){
    		$color = '#DA3B25';
    	}
    	if($status == 'delivered'){
    		$color = '#37691E';
    	}
    	if($status == 'complete'){
    		$color = '#37691E';
    	}
    	if($status == 'canceled'){
    		$color = '#DA3B25';
    	}
    	
    	return $color;
    }
    
    public function getUsedPoints($order){
		
		$purchaseHelper = $this->objectManager->get('\Mirasvit\Rewards\Helper\Purchase');
		$rewardsData = $this->objectManager->get('\Mirasvit\Rewards\Helper\Data');
		
		$purchase = $purchaseHelper->getByOrder($order);
		
		$earn_points_text = '';
		$earn_points = 0;
		$spend_points_amount = '';
		$spend_points = 0;
		if ($purchase) {
            //-$purchase->getBaseSpendAmount();
		    $spend_points_amount = $purchase->getSpendAmount();
		    $spend_points = $purchase->getSpendPoints();

		    $earn_points = $purchase->getEarnPints();
            if(empty($earn_points)){
            	$earn_points = $purchase->getData('earn_points');
            }
            
            $earn_points = !empty($earn_points) ? $earn_points : "0";
            if ($earn_points) {
                $earn_points_text = $rewardsData->formatPoints($earn_points);
            }
        }

        return ['spend_points' => $spend_points, 'spend_points_amount' => $spend_points_amount, 'earned_points' => $earn_points, 'earned_points_text' => $earn_points_text];
    }
}
