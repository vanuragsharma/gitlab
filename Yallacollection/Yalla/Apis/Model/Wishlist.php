<?php

namespace Yalla\Apis\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Wishlist\Controller\WishlistProvider;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;

class Wishlist implements \Yalla\Apis\Api\WishlistInterface
{
    /**
     * @var CollectionFactory
     */
    protected $_wishlistCollectionFactory;
    /**
     * Wishlist item collection
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    protected $_itemCollection;
    /**
     * @var WishlistRepository
     */
    protected $_wishlistRepository;
    /**
     * @var ProductRepository
     */
    protected $_productRepository;
    /**
     * @var WishlistFactory
     */
    protected $_wishlistFactory;
    /**
     * @var Item
     */
    protected $_itemFactory;

	protected $request;
	
	protected $objectManager;
	
	protected $helper;
	
	protected $customerModel;

    /**
     * @param CollectionFactory $wishlistCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistCollectionFactory,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
		\Magento\Framework\App\Request\Http $request
    ) {
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_productRepository = $productRepository;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_itemFactory = $itemFactory;
		$this->request = $request;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $this->objectManager->get('Yalla\Apis\Helper\Data');
        $this->customerModel = $this->objectManager->create('Magento\Customer\Model\Customer');
    }
    
    /**
     * Returns customer's wishlist
     *
     * @param int $customerId
     * @return array
     */
    public function get($customerId)
    {
        $api_auth = $this->objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
		//Multi store view
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
        $this->helper->setStore($lang, $country);
        $storeManager = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currency = $this->request->getParam('currency');
        //echo $currency;die;
        if($currency) {
        	$storeManager->getStore()->setCurrentCurrencyCode($currency);
        }
        //Multi store view

        if (empty($customerId)) {
            echo json_encode(['success' => 'false', 'msg' => __('Missing parameter!')]);
            exit;
        }
        try {
            $customer = $this->customerModel->load($customerId);
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => __('Customer does not exist!')]);
            exit;
        }
        
        $wishlistItems = $this->wishListItems($customerId);

        echo json_encode(['success' => 'true', 'msg' => __('Success'), 'collection' => $wishlistItems]);
        exit;
    }
    
    /**
     * Returns customer's wishlist
     *
     * @param int $customerId
     * @return array
     */
    public function add()
    {
		$api_auth = $this->objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
    	$post = file_get_contents("php://input");
    	$request = [];
        if (!empty($post)) {
            $request = json_decode($post, true);
        }
        
        if (!isset($request['product_id'])) {
            echo json_encode(['success' => 'false', 'msg' => __('Invalid product!')]);
            exit;
        }

		if (!isset($request['customer_id'])) {
            echo json_encode(['success' => 'false', 'msg' => __('Missing parameter!')]);
            exit;
        }
        
        //Multi store view
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
        $this->helper->setStore($lang, $country);
        $storeManager = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currency = $this->request->getParam('currency');
        if($currency) {
        	$storeManager->getStore()->setCurrentCurrencyCode($currency);
        }
        //Multi store view
        
        $productId = $request['product_id'];
    	$customerId = $request['customer_id'];
        try {
            $customer = $this->customerModel->load($customerId);
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => __('Customer does not exist!')]);
            exit;
        }
        
        try {
            $product = $this->_productRepository->getById($productId);
        } catch (\Exception $e) {
        	echo json_encode(['success' => 'false', 'msg' => $e->getMessage()]);
            exit;
        }
        
        try {
	        $customer = $this->customerModel->load($customerId);
	    } catch (\Exception $e) {
	        echo json_encode(['success' => 'false', 'msg' => __('Customer does not exist!')]);
	        exit();
	    }
        try {
            $wishlist = $this->_wishlistFactory->create()->loadByCustomerId($customerId, true);
            $wishlist->addNewItem($product);
            
            $wishlist->save();
        } catch (\Exception $e) {
        	echo json_encode(['success' => 'false', 'msg' => __('Error with wishlist!')]);
	        exit();
        }

        $wishlistItems = $this->wishListItems($customerId);
		$wishlist_item_id = 0;
		foreach($wishlistItems as $wishlistItem){
			if($wishlistItem['product_id'] == $productId){
				$wishlist_item_id = $wishlistItem['wishlist_item_id'];
				break;
			}
		}
        echo json_encode(['success' => 'true', 'msg' => __('Success'), 'wishlist_item_id' => $wishlist_item_id, 'collection' => $wishlistItems]);
        exit;
    }
    
    /**
     * Returns customer's wishlist
     *
     * @param int $customerId
     * @return array
     */
    public function delete()
    {

		$api_auth = $this->objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        $post = file_get_contents("php://input");
    	$request = [];
        if (!empty($post)) {
            $request = json_decode($post, true);
        }
        
        if (!isset($request['wishlist_item_id'])) {
            echo json_encode(['success' => 'false', 'msg' => __('Invalid wishlist item!')]);
            exit;
        }

		if (!isset($request['customer_id'])) {
            echo json_encode(['success' => 'false', 'msg' => __('Missing parameter!')]);
            exit;
        }
        
        $wishlist_item_id = $request['wishlist_item_id'];
    	$customerId = $request['customer_id'];
        try {
            $customer = $this->customerModel->load($customerId);
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => __('Customer does not exist!')]);
            exit;
        }
        
        if (empty($wishlist_item_id)) {
            echo json_encode(['success' => 'false', 'msg' => __('Invalid wishlist item!')]);
            exit;
        }
        
        $wishlist_item = $this->_itemFactory->create()->load($wishlist_item_id);
        if (!$wishlist_item->getId()) {
            echo json_encode(['success' => 'false', 'msg' => __('Invalid wishlist item!')]);
            exit;
        }
        
        $wishlistId = $wishlist_item->getWishlistId();

        $wishlist = $this->_wishlistFactory->create()->load($wishlistId);
        
        if (!$wishlist) {
            echo json_encode(['success' => 'false', 'msg' => __('Invalid wishlist!')]);
            exit;
        }
        
        if ($wishlist->getCustomerId() != $customerId) {
        	echo json_encode(['success' => 'false', 'msg' => __('Invalid wishlist!')]);
            exit;
        }
        try {
            $wishlist_item->delete();
            $wishlist->save();
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => __('Error!')]);
            exit;
        }

        $wishlistItems = $this->wishListItems($customerId);

        echo json_encode(['success' => 'true', 'msg' => __('Success'), 'collection' => $wishlistItems]);
        exit;
    }

    protected function wishListItems($customerId){
        $wishlistCollection = $this->_wishlistCollectionFactory->create()->addCustomerIdFilter($customerId);
        
        $wishlist = [];
        $item_ids = [];
        if(!empty($wishlistCollection)) {
			$productRepo = $this->objectManager->create('Yalla\Apis\Model\ProductRepository');
			
			
            foreach ($wishlistCollection as $item) {
            	$item_ids[$item->getWishlistItemId()] = $item->getProductId();
            }
            
            $collection = $this->objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');
			
			$collection->setFlag('has_stock_status_filter', true);
			$collection->getSelect()->joinLeft( array( 'stock_item' => 'cataloginventory_stock_item' ), 'stock_item.product_id = e.entity_id', array("qty"));
			
			$subquery = new \Zend_Db_Expr('(SELECT sum(`quantity`) as value, sku FROM `inventory_reservation`group by sku)');
			$collection->getSelect()->joinLeft( array( 't' => $subquery ), 'e.sku = t.sku', array());

			$collection->getSelect()->where("abs(t.value) is NULL OR (abs(t.value) < stock_item.qty)");
			if(count($item_ids)){
				$collection->getSelect()->where("e.entity_id in (".implode(',', $item_ids).")");
			}

			$in_stock_items = [];
			foreach ($collection as $product) {
				$in_stock_items[$product->getEntityId()] = $product->getEntityId();
            }
            
            foreach ($wishlistCollection as $item) {
            	if(!isset($in_stock_items[$item->getProductId()])) continue;
            	
                $product = $item->getProduct();
				$product_info = $productRepo->getProductData($product);

                $item_data = [
                    "wishlist_item_id" => $item->getWishlistItemId(),
                    "wishlist_id" => $item->getWishlistId(),
                    "product_id" => $item->getProductId(),
                    "product_info" => $product_info
                ];
                
                $wishlist[] = $item_data;
            }
			
        }

        return $wishlist;
    }
    
}
