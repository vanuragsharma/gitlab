<?php
namespace Yalla\Apis\Model;

use Magento\Quote\Model\Quote\Item;

class Addtocart implements \Yalla\Apis\Api\AddtocartInterface {

    /**
     * @var WishlistFactory
     */
    protected $_wishlistFactory;
    /**
     * @var Item
     */
    protected $_itemFactory;
    protected $request;
    
    public function __construct(
	\Magento\Quote\Model\QuoteFactory $quoteFactory,
	\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
	\Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
	\Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
	\Magento\Wishlist\Model\ItemFactory $itemFactory,
	\Yalla\Apis\Helper\ApiData $apiHelper,
	\Magento\Framework\App\Request\Http $request) {

        $this->_storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->customerRepository = $customerRepository;
        $this->apiHelper = $apiHelper;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_itemFactory = $itemFactory;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $request;
    }

    public function addtocart() {
        $post = file_get_contents("php://input");
		$api_auth = $this->_objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        //Multi store view
        $helper = $this->_objectManager->get('Yalla\Apis\Helper\Data');
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
		$apiHelper = $this->_objectManager->get('Yalla\Apis\Helper\Data');
        $apiHelper->setStore($lang, $country);
        //Multi store view
        if (!empty($post)) {
            $request = json_decode($post, true);
        }

		$response = ['success' => 'true', 'msg' => __('Item has been added successfully to your cart.'), 'collection' => array()];

		if (!isset($request['app_version'])) {
            echo json_encode(array('success' => 'false', 'msg' => __('Please update the App, New version available in Store')));
            exit;
        }
        
		if(!isset($request['customer_id']) || !isset($request['product_id']) || !isset($request['qty'])){
			echo json_encode(['status' => 'error', 'msg' => __('Mandatory parameters are missing.')]);
			exit;
		}

        $customerId = (int) $request['customer_id'];
        $productId = (int) $request['product_id'];
        $parentId = 0;
        if(isset($request['child_id']) && !empty($request['child_id'])){
            $productId = (int) $request['child_id'];
            $parentId = (int) $request['product_id'];
        }

        $qty = (int) $request['qty'];

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => __('Customer does not exist.')]);
            exit;
        }

		if (!$productId || !$qty) {
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
            $cartId = $this->cartManagementInterface->createEmptyCart();
            $quote = $this->cartRepositoryInterface->get($cartId);
            $quote->setStore($store);
            $quote->setCurrency();
            $quote->assignCustomer($customer);
        }

        try {           
	    	if(isset($request['bundle_id']) && !empty($request['bundle_id'])){
				$bundleId = (int) $request['bundle_id'];
				$products = $this->_getProductsAndQtys($bundleId);

				$status = [];
				foreach ($products as $productId => $qty) {
			        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
			        
			        $status = $this->_addProduct($quote, $product, $qty);
			        if($status['success'] == 'false'){
						$response['success'] = 'false';
						$response['msg'] = $status['msg'];
			        }
		    	}
		    	if($response['success'] == 'false'){
		    		echo json_encode($response);
					exit;
		    	}else if(isset($status['quote'])){
		    		$quote = $status['quote'];
		    		$bundleIds = explode(',', $quote->getData('bundle_ids'));
					if (!in_array($bundleId, $bundleIds)) {
						$bundleIds[] = $bundleId;
					}

					if ($bundleIds[0] == '') {
						unset($bundleIds[0]);
					}
					$quote->setData('bundle_ids', implode(',', $bundleIds));
					$quote->save();
		    	}
			}else{
				$_product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);

				if(!$_product->getData('entity_id')){
					echo json_encode(['success' => 'false', 'msg' => __('Product does not exist.')]);
					exit;
				}
				$status = $this->_addProduct($quote, $_product, $qty, $parentId);
		        if($status['success'] == 'false'){
					$response['success'] = 'false';
					$response['msg'] = $status['msg'];
		        }
        	}
			
			if($response['success'] != 'false' && isset($request['wishlist_item_id'])){
				$wishlist_item = $this->_itemFactory->create()->load($request['wishlist_item_id']);
				if ($wishlist_item->getId()) {
				    $wishlistId = $wishlist_item->getWishlistId();

					$wishlist = $this->_wishlistFactory->create()->load($wishlistId);
					
					try {
						$wishlist_item->delete();
						$wishlist->save();
					} catch (\Exception $e) {}
				}
			}
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

        exit;
    }

    /**
     * 
     * @return array
     */
    protected function _addProduct($quote, $_product, $qty, $parentId = 0)
    {
		
		$filter = new \Zend_Filter_LocalizedToNormalized(
			['locale' => $this->_objectManager->get(
			\Magento\Framework\Locale\ResolverInterface::class
			)->getLocale()]
		);
		$cartItem = array();

		$cartItem['qty'] = $filter->filter($qty);

        // Check product stock
		$stockItem = $this->_objectManager->create('\Magento\CatalogInventory\Model\Stock\Item');
        $hasStock = true;

		$message = __('Product is out of stock.');

		if($_product->getTypeId() == 'bundle'){
			if($_product->getIsSalable()){
				$hasStock = true;
				
			}
		}else{
			try{
				$productStock = $stockItem->load($_product->getEntityId(), 'product_id');
				$stockQty = $productStock->getQty();
				if($stockQty){
					if($stockQty < $qty){
						$message = __('Requested quantity not available.');
						$hasStock = false;
					}
				}else{
					$hasStock = false;
				}
			} catch(\Exception $e) {
				$hasStock = false;
			}
		}

        if(!$hasStock){
			return ['success' => 'false', 'msg' => $message];
			exit;
		}

		// Get super attributes
		$itemProductId = 0;
		$super_attributes = [];
		$errorMsg = '';
        if($parentId && $parentId != $_product->getEntityId()){
            $parentProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($parentId);
            
            if($parentProduct->getData('type_id') == 'configurable'){
                    $productAttributeOptions = $parentProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($parentProduct);

                    foreach ($productAttributeOptions as $productAttribute) {
                        $allValues = array_column($productAttribute['values'], 'value_index');
                        $currentProductValue = $_product->getData($productAttribute['attribute_code']);
                        if (in_array($currentProductValue, $allValues)) {
                            $super_attributes[$productAttribute['attribute_id']] = $currentProductValue;
                        }
                    }
                    if(count($super_attributes)){
                        $itemProductId = $parentId;
                    }
             }else{
             	$errorMsg = 'Product '.$_product->getName().' is not available.';
             }
			 if($itemProductId){
				$_product = $parentProduct;
			 }
	 	}
       
        $_product->setPrice($_product->getFinalPrice());
		if(count($super_attributes)){
			$cartItem['super_attribute'] = $super_attributes;
		}

		if($_product->getTypeId() == 'bundle'){
			// Find bundle options and add in cartItem array
			
		    $selectionCollection = $_product->getTypeInstance()
		        ->getSelectionsCollection(
		            $_product->getTypeInstance()->getOptionsIds($_product),
		            $_product
		        );
		    $bundleOptions = [];
			$bundleSelectionQty = [];
		    foreach ($selectionCollection as $selection) {
		        $bundleOptions[$selection->getOptionId()] = (int) $selection->getSelectionId();
				$bundleSelectionQty[$selection->getOptionId()] = 1;
		    }
        	$cartItem['product'] = $_product->getId();
		    $cartItem['bundle_option'] = $bundleOptions;
			//$cartItem['bundle_option_qty'] = $bundleSelectionQty;

		}

		$buyRequest = new \Magento\Framework\DataObject($cartItem);
		$quote->addProduct($_product, $buyRequest);
		$quote->save();
		$quote->collectTotals()->save();
		
		/*if($errorMsg){
			return ['success' => 'false', 'msg' => $errorMsg];
			exit;
		}*/
		return ['success' => 'true', 'msg' => '', 'quote' => $quote];
		exit;
    }
    /**
     * @param $bundleId
     * @return array
     */
    protected function _getProductsAndQtys($bundleId)
    {
        $qtyArray = [];
		$bundlediscount = $this->_objectManager->create('\Magedelight\Bundlediscount\Model\Bundlediscount');
        $bundle = $bundlediscount->load($bundleId);
        $selections = $bundle->getSelections();
        $qtyArray[$bundle->getProductId()] = $bundle->getQty();
        foreach ($selections as $_selection) {
            $qtyArray[$_selection->getProductId()] = $_selection->getQty();
        }
        return $qtyArray;
    }

}

