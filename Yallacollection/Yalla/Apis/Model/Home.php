<?php

namespace Yalla\Apis\Model;

use Yalla\Apis\Api\CategoryManagementInterface;
use Yalla\Apis\Api\HomeInterface;

class Home implements HomeInterface {

    /**
     * @var SliderFactory
     */
    protected $_slideshowCollection;

    /**
     * @var SliderFactory
     */
    protected $_brandCollection;

    /**
     * @var imageHelper
     */
    protected $imageHelper;

    /**
     * @var dataHelper
     */
    protected $dataHelper;

    /**
     * @var categoryManagement
     */
    protected $categoryManagement;
    protected $request;
    protected $_objectManager;
    protected $_filesystem;
    protected $_directoryList;

    public function __construct(
            \Yalla\Apis\Api\CategoryManagementInterface $categoryManagement,
            \Magento\Framework\App\Request\Http $request,
            \Yalla\Apis\Helper\Data $helper
    ) {
        $this->categoryManagement = $categoryManagement;
        $this->request = $request;
        $this->_helper = $helper;
    }

    public function getObjectManager() {
        if (empty($this->_objectManager)) {
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }

        return $this->_objectManager;
    }

    public function getData($customerId) {

		$objectManager = $this->getObjectManager();

	   	 $api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
		        

		$data = ['status' => 'true', 'msg' => 'success'];


        // Getting media baseUrl
        //$objectManager = $this->getObjectManager();

        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');

        //Multi store view
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
		$apiHelper = $objectManager->get('Yalla\Apis\Helper\Data');
        $apiHelper->setStore($lang, $country);
        $currency = $this->request->getParam('currency');
        if($currency) {
        	$storeManager->getStore()->setCurrentCurrencyCode($currency);
        }
        //Multi store view

	$media_url = $objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        // App banners
        //$banners = $objectManager->create('Yalla\MobileBanner\Model\Banners')->getList('T');
        $data['sliders'] = $this->_helper->getBanner(1);

        $categoryManagement = $objectManager->get('\Yalla\Apis\Model\CategoryManagement');
        $data['categories'] = $categoryManagement->getChildCategories(2);

        $imageHelper = $objectManager->get('\Magento\Catalog\Helper\Image');
        $imagewidth = 200;
        $imageheight = 200;

        $data['banners_1'] = $this->_helper->getBanner(2);
        $data['banners_2'] = $this->_helper->getBanner(3);
        $data['banners_3'] = $this->_helper->getBanner(4);
        $data['banners_4'] = $this->_helper->getBanner(5);

        $data['trending']['products'] = $this->_helper->getTrending();
        $data['trending']['category'] = 183;

        $data['new']['products'] = $this->_helper->getNew();
        $data['new']['category'] = 2691; // 181

        $data['featured']['products'] = $this->_helper->getFeatured();
        $data['featured']['category'] = 184;
		
		$data['current_currency'] = $storeManager->getStore()->getCurrentCurrencyCode();
		
		$scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $category_id = $scopeConfig->getValue(
		    'offercategory/general/category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		$data['offer_category_id'] = $category_id;
		
		// Wishlist count
		$data['wishlist_count'] = 0;
		if($customerId){
			$wishlistFactory = $objectManager->create('\Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory');
			$wishlist = $wishlistFactory->create()->addCustomerIdFilter($customerId);

			if($wishlist) {
				$data['wishlist_count'] = (int) $wishlist->getSize();
			}
		}
		
		// Cart Count
		$data['cart_count'] = 0;
		if($customerId){
			$quote = $objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($customerId);
			$totalQuantity = $quote->getItemsQty();
			$data['cart_count'] = (int) $totalQuantity;
	    }
	    
	    $data['languages'][0] = array(
			 'code' => "arabic",
			 'label'=> "Arabic"
		);

		$data['languages'][1] = array(
			 'code' => "english",
			 'label'=> "English"
		);

		$data['country_store'][0] = array(
			 'code' => "qatar",
			 'label'=> "Qatar"
		);
		$data['country_store'][1] = array(
			 'code' => "bahrain",
			 'label'=> "Bahrain"
		);

        $drawerimage = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('imageupload/general2/drawerimage');
        $data['drawer_image'] = $media_url."squareimage/".$drawerimage;

        $response = $data;

        echo json_encode($response);
		exit;
    }
    
    public function checkVersion() {
      
		$objectManager = $this->getObjectManager();

	   	 $api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        $postData = file_get_contents("php://input");
        if (!empty($postData)) {
			$request = json_decode($postData, true);
			$android = 2.3;
			$iphone = 1.7;
           
            if($request['device_type'] == 'android'){
				if($request['version'] >= $android){
                 echo json_encode(array('success' => 'true', 'is_update_available'=>false, 'force_update' => '0', 'msg' => 'OK'));    
            	}else{
                 echo json_encode(array('success' => 'true', 'is_update_available'=>true, 'force_update' => '1', 'msg' => 'There is a newer version available for download!. Please update the app by visiting the Google play store.'));    
				}
			}
            
			if($request['device_type'] == 'iphone'){
				if($request['version'] >= $iphone){
                	echo json_encode(array('success' => 'true', 'is_update_available'=>false, 'force_update' => '0', 'msg' => 'Ok'));   
				}else{
                	echo json_encode(array('success' => 'true', 'is_update_available'=>true, 'force_update' => '1', 'msg' => 'There is a newer version available for download!. Please update the app by visiting the Apple Store.'));   
				}
			}
            
		}else{
	    	echo json_encode(array('success' => 'false', 'msg' => 'Invalid request.'));
            
		}
		exit;
    }

}
