<?php

namespace Yalla\Apis\Model;

use Yalla\Apis\Api\SearchInterface;

class Search implements SearchInterface {

	protected $_request;
	protected $_storeManager;
	protected $_objectManager;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\App\Request\Http $request, array $data = []) {
        $this->_storeManager = $storeManager;
		$this->_request = $request;
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Returns search suggestions
     * @return array
    */
    public function search() {

		$this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$api_auth = $this->objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        $postData = file_get_contents("php://input");
        if (!empty($postData)) {
            $request = json_decode($postData, true);
        }

		if(!isset($request['term'])){
			echo json_encode(['success' => 'false', 'msg' => __('Invalid search term!')]);
			exit;
		}
		
        $term = urldecode(strip_tags($request['term']));

		if(empty($term)){
			echo json_encode(['success' => 'false', 'msg' => __('Invalid search term!')]);
			exit;
		}

		
		//Multi store view
        $lang = $this->_request->getParam('lang');
		$country = $this->_request->getParam('country');
        $apiHelper = $this->objectManager->get('Yalla\Apis\Helper\Data');
        $apiHelper->setStore($lang, $country);
        $currency = $this->_request->getParam('currency');
        if($currency) {
        	$this->_storeManager->getStore()->setCurrentCurrencyCode($currency);
        }
        //Multi store view

        $search_results = [];

        //create our filter
	    /*$filter_1 = $this->objectManager
	            ->create('Magento\Framework\Api\FilterBuilder')
	            ->setField('name')
	            ->setConditionType('like')
	            ->setValue('%' . $term . '%')
	            ->create();

		$filter_2 = $this->objectManager
	            ->create('Magento\Framework\Api\FilterBuilder')
	            ->setField('sku')
	            ->setConditionType('like')
	            ->setValue('%' . $term)
	            ->create();

        $filter_3 = $this->objectManager
                ->create('Magento\Framework\Api\FilterBuilder')
                ->setField('status')
                ->setConditionType('eq')
                ->setValue(1)
                ->create();
        $filter_4 = $this->objectManager
                ->create('Magento\Framework\Api\FilterBuilder')
                ->setField('visibility')
                ->setConditionType('neq')
                ->setValue(1)
                ->create();

        //add our filter(s) to a group
        $filter_group = $this->objectManager
                ->create('Magento\Framework\Api\Search\FilterGroupBuilder')
                ->addFilter($filter_1)
                ->addFilter($filter_2)
                ->create();
        
        $filter_group_1 = $this->objectManager
                ->create('Magento\Framework\Api\Search\FilterGroupBuilder')
                ->addFilter($filter_3)
                ->create();
        $filter_group_2 = $this->objectManager
                ->create('Magento\Framework\Api\Search\FilterGroupBuilder')
                ->addFilter($filter_4)
                ->create();

        $search_criteria = $this->objectManager
                ->create('Magento\Framework\Api\SearchCriteriaBuilder')
                ->setFilterGroups([$filter_group, $filter_group_1, $filter_group_2])
                ->create();

		$search_criteria->setPageSize(50);
		$search_criteria->setCurrentPage(1);
		
		$sortOrderBuilder = $this->objectManager->create('\Magento\Framework\Api\SortOrderBuilder');
		$sortOrder = $sortOrderBuilder->setField('entity_id')->setDirection('DESC')->create();
		$search_criteria->setSortOrders([$sortOrder]);*/
		
		
		$collection = $this->objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');
		$collection->addTaxPercents()
                ->addAttributeToSelect('*')
//                ->addAttributeToSelect('image')
//                ->addAttributeToSelect('small_image')
//                ->addAttributeToSelect('thumbnail')
                //->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addUrlRewrite();
                
//        $collection = $collection->addPriceData(1, 1);
        
        $collection->addAttributeToFilter(array(
        		array('attribute' => 'name', 'like' => '%' . $term . '%'),
        		array('attribute' => 'sku', 'like' => '%' . $term . '%'),
        	)	
        );
		
		$bundle_visibility = $this->objectManager->create('\Magento\Catalog\Model\Product\Visibility');
		// var_dump($bundle_visibility->getVisibleInSiteIds());die();
		$collection->setVisibility($bundle_visibility->getVisibleInSiteIds());

        //$collection->addAttributeToFilter('sku', array('like' => '%' . $term . '%'));
        
		$collection->setFlag('has_stock_status_filter', true);
		$collection->getSelect()->joinLeft( array( 'stock_item' => 'cataloginventory_stock_item' ), 'stock_item.product_id = e.entity_id', array("qty"));
		
		$subquery = new \Zend_Db_Expr('(SELECT sum(`quantity`) as value, sku FROM `inventory_reservation`group by sku)');
		$collection->getSelect()->joinLeft( array( 't' => $subquery ), 'e.sku = t.sku', array());

		$collection->getSelect()->where(" abs(t.value) is NULL OR (abs(t.value) < stock_item.qty) ");
		
		$collection->getSelect()->where("stock_item.backorders != 300");
		$collection->getSelect()->limit(50, 1);
		
        //$productdata = $this->objectManager->create('\Magento\Catalog\Model\ProductRepository')->getList($search_criteria)->getItems();
		$websiteId = $this->_storeManager->getStore(true)->getWebsiteId();
		$priceHelper = $this->objectManager->get('Magento\Framework\Pricing\Helper\Data');
		$product_helper = $this->objectManager->get('Magento\Catalog\Helper\Image');
		$stockItem = $this->objectManager->create('\Magento\CatalogInventory\Api\StockStateInterface');
		
        foreach ($collection as $product) {
			$stock = $stockItem->getStockQty($product->getEntityId(), $websiteId);
            if ($stock>0) {
            	
        		$final_price = $priceHelper->currency($product->getFinalPrice(), true, false);
        
        		$price = $priceHelper->currency($product->getPriceInfo()->getPrice('regular_price')->getValue(), true, false);

            	$imageUrl = $product_helper->getDefaultPlaceholderUrl('image');
				if($product->getData('image')){
					$imageUrl = $product_helper->init($product, 'category_page_list')->setImageFile($product->getData('image'))->resize(150,150)->getUrl();
				}

                $search_results[] = array(
                    'id' => $product->getEntityId(),
                    'name' => $product->getName(),
                    'product_count' => 1,
                    'type' => 'P',
					'image' => $imageUrl,
					'price' => $final_price,
					'regular_price' => $price
                );
            }
        }
            
        $category = false;
		if(!empty($term) && $category){
		    // Start category
		    $collection = $this->objectManager->create('Magento\Catalog\Model\Category')
		            ->getCollection()
		            ->addOrderField('name')
		            ->addAttributeToFilter('is_active', 1)
		            ->addAttributeToFilter('name', array('like' => '%' . $term . '%')); // you can change name to your attribute code

			$storeManager = $this->objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		    foreach ($collection as $category) {
		        $_categoryModel = $this->objectManager->create('Magento\Catalog\Model\Category');
		        
				$_category = $_categoryModel->load($category->getEntityId());
				// echo $category->getPath();
				$category_path = $category->getName();
				$parent_id = 0;
				try{
					$parentCategories = $_category->getParentCategories();
					if($parentCategories){
						$parent_path = '';
						foreach ($parentCategories as $parent) {
							$parent_path .= (empty($parent_path) ? $parent->getName() : " > ".$parent->getName());
						}
						//$parent_id = $parent->getEntityId();
						$category_path = $parent_path;
						
					}
				}catch(\Exception $ex){
					// do nothing
				}
			
				$image = $_category->getCategoryBanner();
				$url = '';
				if ($image) {
				    if (is_string($image)) {
				        $url = $storeManager->getStore()->getBaseUrl() .$image;
				        if(strpos($url, 'pub/media/catalog')){
				        	
				        }else{
				        	$url = '';
				        }
				    }
				}
		        $search_results[] = array(
		            'id' => $category->getEntityId(),
		            'name' => $category_path,
		            'product_count' => $category->getProductCount(),
		            'type' => 'C',
		            'image' => $url
		        );
		    }
		    // End category
        }

        $msg = __('No match found!');
        if(count($search_results)){
			$msg = __('Search result found.');
		}

		echo json_encode(['success' => 'true', 'msg' => $msg, 'collection' => $search_results]);
		exit;
    }

}


