<?php

namespace Yalla\Apis\Model;

use Yalla\Apis\Api\CartInterface;
use Magento\Framework\App\ObjectManager;

class Cart implements CartInterface {

    private $_objectManager;
    protected $request;
    private $wishlistCollectionFactory;

    public function __construct(
	\Magento\Quote\Model\QuoteFactory $quoteFactory,
	\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
	\Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
	\Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magento\Framework\App\Request\Http $request,
	\Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistCollectionFactory) {

		$this->_storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->customerRepository = $customerRepository;
		$this->request = $request;
		$this->wishlistCollectionFactory = $wishlistCollectionFactory;
    }
    
    /**
     * Returns customer Cart
     *
     * @param int $customerId
     * @return array
     */
    public function getCartData($customerId) {

		$response = ['success' => 'true', 'msg' => __('Success'), 'collection' => array()];

        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$api_auth = $this->_objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
		//Multi store view
        //$helper = $this->_objectManager->get('Yalla\Apis\Helper\Data');
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
		$apiHelper = $this->_objectManager->get('Yalla\Apis\Helper\Data');
        $apiHelper->setStore($lang, $country);
        $currency = $this->request->getParam('currency');
        //var_dump($currency);die;
        if($currency) {
        	$this->_storeManager->getStore()->setCurrentCurrencyCode($currency);
            
        }
       // $data['current_currency'] = $storeManager->getStore()->getCurrentCurrencyCode();
        //var_dump($data['current_currency']);die;
        //Multi store view
        try {
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => 'Customer does not exist!']);
            exit;
        }

        try {
            $quote = $this->_objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($customerId);

            $billingAddress = $quote->getBillingAddress();
            $quote_billing_address = $billingAddress->getData();

            $shippingAddress = $quote->getShippingAddress();
            $quote_shipping_address = $shippingAddress->getData();

            $quoteItems = $quote->getAllVisibleItems();
            $cart_items = $this->cartItemsFormat($quoteItems, $customerId);
            if (!count($cart_items)) {
                echo json_encode(['success' => 'false', 'msg' => "Your cart is empty!"]);
				exit;
            }

            $billing_address = [];
            if (!empty($quote_billing_address['customer_address_id'])) {
                $billing_address = [[
                    'firstname' => (isset($quote_billing_address['firstname']) ? $quote_billing_address['firstname'] : ''),
                    'lastname' => (isset($quote_billing_address['lastname']) ? $quote_billing_address['lastname'] : ''),
                    'street' => (isset($quote_billing_address['street']) ? $quote_billing_address['street'] : ''),
                    'city' => (isset($quote_billing_address['city']) ? $quote_billing_address['city'] : ''),
                    'country_id' => (isset($quote_billing_address['country_id']) ? $quote_billing_address['country_id'] : ''),
                    'region' => (isset($quote_billing_address['region']) ? $quote_billing_address['region'] : ''),
                    'region' => (isset($quote_billing_address['region_id']) ? $quote_billing_address['region_id'] : ''),
                    'postcode' => (isset($quote_billing_address['postcode']) ? $quote_billing_address['postcode'] : ''),
                    'telephone' => (isset($quote_billing_address['telephone']) ? $quote_billing_address['telephone'] : '')
                ]];
            }

		$shipping_address = [];
            if (!empty($quote_shipping_address['customer_address_id'])) {
                $shipping_address = [[
                    'firstname' => (isset($quote_shipping_address['firstname']) ? $quote_shipping_address['firstname'] : ''),
                    'lastname' => (isset($quote_shipping_address['lastname']) ? $quote_shipping_address['lastname'] : ''),
                    'street' => (isset($quote_shipping_address['street']) ? $quote_shipping_address['street'] : ''),
                    'city' => (isset($quote_shipping_address['city']) ? $quote_shipping_address['city'] : ''),
                    'country_id' => (isset($quote_shipping_address['country_id']) ? $quote_shipping_address['country_id'] : ''),
                    'region' => (isset($quote_shipping_address['region']) ? $quote_shipping_address['region'] : ''),
                    'region' => (isset($quote_shipping_address['region_id']) ? $quote_shipping_address['region_id'] : ''),
                    'postcode' => (isset($quote_shipping_address['postcode']) ? $quote_shipping_address['postcode'] : ''),
                    'telephone' => (isset($quote_shipping_address['telephone']) ? $quote_shipping_address['telephone'] : '')
                ]];
            }

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

            // Changes for dynamic shipping method
            $tax = $quote->getShippingAddress()->getTaxAmount();
            $quote->save();
            $quote->collectTotals()->save(); //  Update cart total and items

	    	$totalQuantity = $quote->getItemsQty();
            $discount_amount = $quote->getBaseSubtotal() - $quote->getBaseSubtotalWithDiscount();

		    $priceHelper = $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data');

            $response['collection']['summary']['total'] = $priceHelper->currency($quote->getBaseGrandTotal(), true, false);
            $response['collection']['summary']['sub_total'] = $priceHelper->currency($quote->getBaseSubtotal(), true, false);
            $response['collection']['summary']['subtotal_with_discount'] = $priceHelper->currency($quote->getBaseSubtotalWithDiscount(), true, false);
            $response['collection']['summary']['subtotal_withoutcurrency_discount'] = (string) str_replace(",", "", number_format($quote->getBaseSubtotalWithDiscount(), 2));
            $response['collection']['summary']['discount_amount'] = $priceHelper->currency($discount_amount, true, false);
            $response['collection']['summary']['shipping_amount'] =  $priceHelper->currency($shipping_rate, true, false);
            $response['collection']['summary']['tax'] = $priceHelper->currency($tax, true, false);;
            $response['collection']['cart_items_qty'] = $totalQuantity;
            $response['collection']['cart_items'] = $cart_items;
            $response['collection']['possible_reward_points'] = $apiHelper->getQuoteRewardPoints($quote);
            //$response['collection']['billing_address'] = $billing_address;
            //$response['collection']['shipping_address'] = $shipping_address;

            echo json_encode($response);
	    exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => $e->getMessage()]);
	    exit;
        }
    }

    public function cartItemsFormat($quoteItems, $customerId) {
        $data = [];
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $imageHelper = $this->_objectManager->get('\Magento\Catalog\Helper\Image');

        foreach ($quoteItems as $item) {
          $product = $item->getProduct();

		  $_product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());

			$character = (!empty($_product->getAttributeText('characters')) ? $_product->getAttributeText('characters') : '');
			
         $department = $_product->getAttributeText('department');
		 if(!$department){
		   $department = '';
		 }

 		 $category_name = $_product->getAttributeText('product_category');
		 if(!$category_name){
		   $category_name = '';
		 }
				
         $sub_category_name = $_product->getAttributeText('product_subcategory');
		 if(!$sub_category_name){
		   $sub_category_name = '';
		 }

		  $imageUrl = $imageHelper->init($_product, 'small_image')->setImageFile($_product->getData('image'))->getUrl();

		      // Code added to check product availability
		      $hasStock = true;
		  $qty = 0;
		        
		  $stockItem = $this->_objectManager->create('\Magento\CatalogInventory\Model\Stock\Item');
			if($_product->getTypeId() == 'bundle'){
			if($_product->getIsSalable()){
				$hasStock = true;
				
			}}
			else{
		  try{
			$productStock = $stockItem->load($product->getEntityId(), 'product_id');

			$qty = $productStock->getQty();
			if($qty){
				if($qty < $item->getQty()){
		                         $hasStock = false;
		                     }
			}else{
				 $hasStock = false;
			}
		   } catch(\Exception $e) {
			$hasStock = false;
		   }
		   }
		   $wishlist_item_id = "0";
		   if($customerId){
		   	$wishlist = $this->wishlistCollectionFactory->create()->addCustomerIdFilter($customerId)->addFieldToFilter('product_id', $product->getEntityId());
			if($wishlist){
				foreach($wishlist as $wishlist_item){
					$wishlist_item_id = $wishlist_item->getData('wishlist_item_id');
				}
			}
		   }

		   $priceHelper = $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data');
		   $discount = $_product->getPrice() - $_product->getFinalPrice();
		   $d = array(
			'item_id' => $item->getItemId(),
                'id' => $product->getEntityId(),
                'sku' => $product->getSku(),
                'type' => $product->getTypeId(),
                'name' => $_product->getName(),
				'qty' => $item->getQty(),
				'regular_price_value' => str_replace(",", "", number_format($_product->getPrice(),2)),
                'final_price_value' => str_replace(",", "", number_format($_product->getFinalPrice(),2)),
                'brand' => $_product->getAttributeText('brands'),
	            'character' => $character,
                'size' => "",
                'color' => "",
                'discount' => number_format($discount,2),
                'regular_price' => $priceHelper->currency($_product->getPrice(), true, false),
                'final_price' => $priceHelper->currency($_product->getFinalPrice(), true, false),
                'description' => (!empty($_product->getDescription()) ? $_product->getDescription() : ''),
                'short_description' => (!empty($_product->getShortDescription()) ? $_product->getShortDescription() : ''),
                'image' => $imageUrl,
                'is_salable' => $hasStock,
                'options' => isset($options['info_buyRequest']) ? [$options['info_buyRequest']] : [],
				'remaining_qty' => $qty,
				'wishlist_item_id' => $wishlist_item_id,
				'department' => $department,
                'department' => $department,
                'category_name' => $category_name,
                'sub_category_name' => $sub_category_name
            );
            
            array_push($data, $d);
        }

        return $data;
    }

    public function updateCartItem(){

	$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$api_auth = $this->_objectManager->create('\Yalla\Apis\Helper\APIAuth');

	$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
	$post = file_get_contents("php://input");
        if (!empty($post)) {
            $request = json_decode($post, true);
        }

	$response = ['success' => 'true', 'msg' => __('Your cart item has been updated.'), 'collection' => array()];

	if(!isset($request['customer_id']) || !isset($request['cart_item_id']) || !isset($request['qty'])){
	    echo json_encode(['success' => 'false', 'msg' => __('Mandatory parameters are missing.')]);
	    exit;
	}

        $customerId = (int) $request['customer_id'];
        $cartItemId = (int) $request['cart_item_id'];
	$parentId = (int) (isset($request['parent_id']) ? $request['parent_id'] : 0);
        $qty = (int) $request['qty'];

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => __('Customer does not exist!')]);
            exit();
        }

	if (!$cartItemId) {
            echo json_encode(['success' => 'false', 'msg' => __('Invalid request data.')]);
            exit;
	}

        $store = $this->_storeManager->getStore();

       

	// Get customer current quote
        $quote = $this->quoteFactory->create()->getCollection()
                ->addFieldToFilter('is_active', true)
                ->addFieldToFilter('customer_id', $customerId);

        $quoteData = $quote->getData();
        if (isset($quoteData[0]['entity_id'])) {
            $quote = $this->cartRepositoryInterface->get($quoteData[0]['entity_id']);
        }

        // Create cart if quote does not exist
        if (!count($quoteData)) {
            $cartId = $this->cartManagementInterface->createEmptyCart(); //Create empty cart
            $quote = $this->cartRepositoryInterface->get($cartId); // load empty cart quote
            $quote->setStore($store);
            $quote->setCurrency();
            $quote->assignCustomer($customer); //Assign quote to customer
        }

        try {
            $errorMsgs = '';

            $StockState = $this->_objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
            $stockItem = $this->_objectManager->create('\Magento\CatalogInventory\Model\Stock\Item');
            
            try {
                $hasStock = true;
		$quoteItem = $quote->getItemById($cartItemId); // Get product by cart item id

		if(!$quoteItem){
			echo json_encode(['success' => 'false', 'msg' => __('Invalid item update request.')]);
			exit;
		}

                $itemProduct = $quoteItem->getProduct();
                $productName = $itemProduct->getName();
                
		try{
		    $productStock = $stockItem->load($itemProduct->getId(), 'product_id');

		    $stockQty = $productStock->getQty();
		    if($stockQty){
			if($stockQty < $qty){
			    $hasStock = false;
			}
		    }else{
			$hasStock = false;
		    }
		} catch(\Exception $e) {
		    $hasStock = false;
		}
                
                if(!$hasStock){
			echo json_encode(['success' => 'false', 'msg' => __('Product is out of stock.')]);
			exit;
		}
                
                if($quoteItem){
                    $quoteItem->setQty((double) $qty);
                    $quoteItem->save();
        	}
            } catch (\Exception $e) {
		echo json_encode(['success' => 'false', 'msg' => $e->getMessage()]);
		exit;
            }

            $quote->save();
            $quote->collectTotals()->save();

	    $quote = $this->_objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($customerId);
	    //$totalItems = $cart->getQuote()->getItemsCount();
	    $totalQuantity = $quote->getItemsQty();

	    $priceHelper = $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data');

            $response['collection']['cart_subtotal'] = $priceHelper->currency($quote->getBaseSubtotal(), true, false);
            $response['collection']['cart_items_qty'] = (int) $totalQuantity;
            echo json_encode($response);
	    exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => $e->getMessage()]);
	    exit;
        }
    }

    public function deleteCartItem(){

	$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$api_auth = $this->_objectManager->create('\Yalla\Apis\Helper\APIAuth');

	$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
	$post = file_get_contents("php://input");
        if (!empty($post)) {
            $request = json_decode($post, true);
        }

	$response = ['success' => 'true', 'msg' => __('Your cart item has been removed.'), 'collection' => array()];

	if(!isset($request['customer_id']) || !isset($request['cart_item_id'])){
	    echo json_encode(['status' => 'error', 'msg' => __('Mandatory parameters are missing.')]);
	    exit;
	}

        $customerId = (int) $request['customer_id'];
        $cartItemId = (int) $request['cart_item_id'];

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => __('Customer does not exist!')]);
	    exit;
        }

	// Get customer current quote
        $quote = $this->quoteFactory->create()->getCollection()
                ->addFieldToFilter('is_active', true)
                ->addFieldToFilter('customer_id', $customerId);

        $quoteData = $quote->getData();
        if (isset($quoteData[0]['entity_id'])) {
            $quote = $this->cartRepositoryInterface->get($quoteData[0]['entity_id']);
        }

        if (empty($quote)) {
	    echo json_encode(['success' => 'false', 'msg' => __('Customer cart does not exist.')]);
	    exit;
	}
        if (!empty($quote)) {
            $quoteItem = '';

            foreach ($quote->getAllVisibleItems() as $item) {
                if ($item->getData('item_id') == $cartItemId) {
                    $quoteItem = $item;
                }
            }
            
            if (empty($quoteItem)) {
                echo json_encode(['success' => 'false', 'msg' => __('Cart item does not exist.')]);
		exit;
            }
            
            $itemId = $quoteItem->getId();
            
            try {
                $quote->removeItem($itemId);
                $quote->save();
                $quote->collectTotals()->save();

        	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$quote = $objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($customerId);
		//$totalItems = $cart->getQuote()->getItemsCount();
		$totalQuantity = $quote->getItemsQty();

		$priceHelper = $objectManager->get('Magento\Framework\Pricing\Helper\Data');

		$response['collection']['cart_subtotal'] = $priceHelper->currency($quote->getBaseSubtotal(), true, false);
		$response['collection']['cart_items_qty'] = (int) $totalQuantity;
            	echo json_encode($response);
		exit;
            } catch (\Exception $e) {
                echo json_encode(['success' => 'false', 'msg' => 'Could not remove item from cart']);
		exit;
            }
        }

        echo json_encode(['success' => 'false', 'msg' => 'Could not remove item from cart']);
	exit;
    }
}

