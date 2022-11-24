<?php

namespace Yalla\Apis\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper {

	protected $locale;
	protected $_dir;
	protected $_objectManager;
	protected $scopeConfig;
	
	public function __construct(
	    \Magento\Framework\Filesystem\DirectoryList $dir
	) {
	    $this->_dir = $dir;
	    $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $this->scopeConfig = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
	}

	public function getWeightUnit()
	{
		return $this->scopeConfig->getValue(
		    'general/locale/weight_unit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}
	
    public function setStore($lang = "english", $country = "qatar"){
        $lang = strtolower($lang);
		$country = strtolower($country);
        $storeRepo = $this->_objectManager->get('\Magento\Store\Model\Store');
        
        $country_language = $country . '_' . $lang;
        
        $store = $storeRepo->load($country_language, 'code');
        //$store = $storeRepo->load($lang, 'name');

        $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeManager->setCurrentStore($store->getId());

        $this->locale = $this->scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $store->getCode()
        );

	$storeManager->getStore()->setCurrentCurrencyCode('QAR');

	$localeInterface = $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface');
	$localeInterface->setLocale($this->locale);
	$localeInterface->setDefaultLocale($this->locale);

	return $store->getId();
           
    }

    public function getTrending()
    {
    	$collection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $media_url = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

    	$getTrendingProducts = $collection->addMinimalPrice()
    			->addAttributeToSelect('*')
                /*->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect('app_image')*/
                ->addUrlRewrite() 
		->addAttributeToFilter('tags', array('finset' => array('5515')));
                //->addAttributeToFilter('is_trending', 1, 'left');
                
        $getTrendingProducts->setFlag('has_stock_status_filter', true);
		$getTrendingProducts->getSelect()->joinLeft( array( 'stock_item' => 'cataloginventory_stock_item' ), 'stock_item.product_id = e.entity_id', array("qty","backorders"));
		
		$subquery = new \Zend_Db_Expr('(SELECT sum(`quantity`) as value, sku FROM `inventory_reservation`group by sku)');
		$getTrendingProducts->getSelect()->joinLeft( array( 't' => $subquery ), 'e.sku = t.sku', array());

		$getTrendingProducts->getSelect()->where("abs(t.value) is NULL OR (abs(t.value) < stock_item.qty)");
		
		$getTrendingProducts->getSelect()->where("stock_item.backorders != 300");
		
        $getTrendingProducts->getSelect()
            ->order('rand()')
            ->limit(20);

        $trendingproducts = array();

		$priceHelper = $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data');
		$store_obj	= $this->_objectManager->create('\Magento\Store\Model\StoreManagerInterface');
		$websiteId = $store_obj->getStore(true)->getWebsiteId();
    	foreach( $getTrendingProducts as $product )
    	{
			$productDetails = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product->getEntityId());
			if (!$productDetails->getIsSalable())
				continue;
			/* $stockItem = $this->_objectManager->create('\Magento\CatalogInventory\Api\StockStateInterface');
			$stock = $stockItem->getStockQty($product->getEntityId(), $websiteId);
							//echo $stock;
							if($stock>0){
								$inStock = true;

							}
							else{
								$inStock = false;
								break;
							} */
    		$image = $product->getResource()->getAttribute('app_image')->getFrontend()->getValue($product);
    		if(empty($image)){
    			$image = $product->getImage();
    		}
    		$image = $media_url.'catalog/product'.$image;
    		
    		$product_tag = $product->getTags();
					
		    $product_tags = explode(",", $product_tag);
		    $trending_label = '';
		    $exclusive_label = '';
		    $featured_label = '';
		    
		    foreach($product_tags as $tag){
		        if($tag == '5515'){ // Trending
		        	$trending_label = __('Trending');
		        }
		        if($tag == '5513'){ // Exclusive
		           $exclusive_label = __('Exclusive');
		        }
		        if($tag == '5416'){ // Featured
		           $featured_label = __('Featured');
		        }
		    }
		    
    		$trendingproducts[] = array(
    			"id" => $product->getId(),
	                 "sku" => $product->getSku(),
	                 "type" => $product->getTypeId(),
	                 "title" => $product->getName(),
	                 "regular_price_value" => (string) $product->getPrice(),
	                 "final_price_value" => (string) $product->getFinalPrice(),
	                 "regular_price" => $priceHelper->currency($product->getPrice(), true, false),
	                 "final_price" => $priceHelper->currency($product->getFinalPrice(), true, false),
	                 "image" => $image,
	                 "salable" => $productDetails->getIsSalable(),
	                 "has_options" => $product->hasCustomOptions(),
	                 'trending_label' => $trending_label,
				    'exclusive_label' => $exclusive_label,
				    'featured_label' => $featured_label
    			);
    	}

    	return $trendingproducts;
    }

    public function getFeatured()
    {
    	$collection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $media_url = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

    	$getFeaturedProducts = $collection->addMinimalPrice()
    			->addAttributeToSelect('*')
                /*->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect('app_image')*/
                ->addUrlRewrite() 
		->addAttributeToFilter('tags', array('finset' => array('5516')));
                //->addAttributeToFilter('sw_featured', 1, 'left');

		$getFeaturedProducts->setFlag('has_stock_status_filter', true);
		$getFeaturedProducts->getSelect()->joinLeft( array( 'stock_item' => 'cataloginventory_stock_item' ), 'stock_item.product_id = e.entity_id', array("qty","backorders"));
		
		$subquery = new \Zend_Db_Expr('(SELECT sum(`quantity`) as value, sku FROM `inventory_reservation`group by sku)');
		$getFeaturedProducts->getSelect()->joinLeft( array( 't' => $subquery ), 'e.sku = t.sku', array());

		$getFeaturedProducts->getSelect()->where("abs(t.value) is NULL OR (abs(t.value) < stock_item.qty)");
		
		$getFeaturedProducts->getSelect()->where("stock_item.backorders != 300");
		
        $getFeaturedProducts->getSelect()
            ->order('rand()')
            ->limit(20);
                
        $featuredproducts = array();

	$priceHelper = $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data');
	$store_obj	= $this->_objectManager->create('\Magento\Store\Model\StoreManagerInterface');
	$websiteId = $store_obj->getStore(true)->getWebsiteId();

    	foreach( $getFeaturedProducts as $products )
    	{
			$productDetails = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($products->getEntityId());
				//$inStock = true;
				/*if (!$productDetails->getIsSalable())
				continue;*/
			/*$stockItem = $this->_objectManager->create('\Magento\CatalogInventory\Api\StockStateInterface');
			$stock = $stockItem->getStockQty($products->getEntityId(), $websiteId);
							//echo $stock;
							if($stock>0){
								$inStock = true;

							}
							else{
								$inStock = false;
								break;
							} */
			if (!$productDetails->getIsSalable())
				continue;
    		$image = $products->getResource()->getAttribute('app_image')->getFrontend()->getValue($products);
    		if(empty($image)){
    			$image = $products->getImage();
    		}
    		$image = $media_url.'catalog/product'.$image;
    		
    		$product_tag = $products->getTags();
					
		    $product_tags = explode(",", $product_tag);
		    $trending_label = '';
		    $exclusive_label = '';
		    $featured_label = '';
		    
		    foreach($product_tags as $tag){
		        if($tag == '5515'){ // Trending
		        	$trending_label = __('Trending');
		        }
		        if($tag == '5513'){ // Exclusive
		           $exclusive_label = __('Exclusive');
		        }
		        if($tag == '5416'){ // Featured
		           $featured_label = __('Featured');
		        }
		    }
		    
    		$featuredproducts[] = array(
    				 "id" => $products->getId(),
	                 "sku" => $products->getSku(),
	                 "type" => $products->getTypeId(),
	                 "title" => $products->getName(),
	                 "regular_price_value" => (string) $products->getPrice(),
	                 "final_price_value" => (string) $products->getFinalPrice(),
	                 "regular_price" => $priceHelper->currency($products->getPrice(), true, false),
	                 "final_price" => $priceHelper->currency($products->getFinalPrice(), true, false),
	                 "image" => $image,
	                 "salable" =>$productDetails->getIsSalable(),
	                 "has_options" => $products->hasCustomOptions(),
	                 'trending_label' => $trending_label,
				    'exclusive_label' => $exclusive_label,
				    'featured_label' => $featured_label
    			);
    	}

    	return $featuredproducts;
    }

    public function getNew()
    {
    	$collection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $media_url = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
			
    	$getNewProducts =$collection->addMinimalPrice()
    			->addAttributeToSelect('*')
                /*->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect('app_image')*/
                ->addUrlRewrite()
		->addAttributeToFilter('tags', array('finset' => array('5512')));
                //->addAttributeToSort('created_at','desc');

		$getNewProducts->setFlag('has_stock_status_filter', true);
		$getNewProducts->getSelect()->joinLeft( array( 'stock_item' => 'cataloginventory_stock_item' ), 'stock_item.product_id = e.entity_id', array("qty","backorders"));
		
		$subquery = new \Zend_Db_Expr('(SELECT sum(`quantity`) as value, sku FROM `inventory_reservation`group by sku)');
		$getNewProducts->getSelect()->joinLeft( array( 't' => $subquery ), 'e.sku = t.sku', array());

		$getNewProducts->getSelect()->where("abs(t.value) is NULL OR (abs(t.value) < stock_item.qty)");
		
		$getNewProducts->getSelect()->where("stock_item.backorders != 300");
		
		$getNewProducts->getSelect()
                //->order('created_at','desc')
		 ->order('rand()')
                ->limit(20);
                
        $newproducts = array();

	$priceHelper = $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data');
	$store_obj	= $this->_objectManager->create('\Magento\Store\Model\StoreManagerInterface');
	$websiteId = $store_obj->getStore(true)->getWebsiteId();

    	foreach( $getNewProducts as $products )
    	{
			/*$stockItem = $this->_objectManager->create('\Magento\CatalogInventory\Api\StockStateInterface');
			$stock = $stockItem->getStockQty($products->getEntityId(), $websiteId);
							//echo $stock;
							if($stock>0){
								$inStock = true;

							}
							else{
								$inStock = false;
								break;
							} */
			$productDetails = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($products->getEntityId());
				//$inStock = true;
				if (!$productDetails->getIsSalable())
				continue;
    		$image = $products->getResource()->getAttribute('app_image')->getFrontend()->getValue($products);
    		if(empty($image)){
    			$image = $products->getImage();
    		}
    		$image = $media_url.'catalog/product'.$image;
    		
    		$product_tag = $products->getTags();
					
		    $product_tags = explode(",", $product_tag);
		    $trending_label = '';
		    $exclusive_label = '';
		    $featured_label = '';
		    
		    foreach($product_tags as $tag){
		        if($tag == '5515'){ // Trending
		        	$trending_label = __('Trending');
		        }
		        if($tag == '5513'){ // Exclusive
		           $exclusive_label = __('Exclusive');
		        }
		        if($tag == '5416'){ // Featured
		           $featured_label = __('Featured');
		        }
		    }
		    
    		$newproducts[] = array(
    				 "id" => $products->getId(),
	                 "sku" => $products->getSku(),
	                 "type" => $products->getTypeId(),
	                 "title" => $products->getName(),
	                 "regular_price_value" => (string) $products->getPrice(),
	                 "final_price_value" => (string) $products->getFinalPrice(),
	                 "regular_price" => $priceHelper->currency($products->getPrice(), true, false),
	                 "final_price" => $priceHelper->currency($products->getFinalPrice(), true, false),
	                 "image" => $image,
	                 "salable" => $productDetails->getIsSalable(),
	                 "has_options" => $products->hasCustomOptions(),
	                 'trending_label' => $trending_label,
				    'exclusive_label' => $exclusive_label,
				    'featured_label' => $featured_label
    			);
    	}

    	return $newproducts;
    }

    public function getBanner( $banner_position )
    {
    	$collection = $this->_objectManager->create('Yalla\Appbanners\Model\ResourceModel\Banners\Collection');
		$store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore();
        $store_id = $store->getId();
        $media_url = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $getBanners =$collection->addFieldtoFilter('status', 1 )
		->addFieldtoFilter('banner_position', $banner_position)
                ->addFieldtoFilter('store_ids', array(
					array('like' => "%0%"),
					array('like' => "%".$store_id."%")
				));

        $banners = array();
	

        foreach ($getBanners as $banner) {
        	 $banners[] = array(
                'link_id' => $banner['banner_id'],
                'link_type' => $banner['banner_type'],
                'link_type_id' => $banner['banner_type_id'],
                'title' => $banner['banner_name'],
                'url' => $media_url.'appbanners_logo/'.$banner['banner_logo']
            );  
        
        }

        return $banners;
    }

    public function cartItemsFormat($quoteItems) {
        $data = [];
        $imageHelper = $this->_objectManager->get('\Magento\Catalog\Helper\Image');

        foreach ($quoteItems as $item) {
          $product = $item->getProduct();

	  	  $_product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
		  $subtotal = $item->getSubtotal();
      	  $product_quantity = $item->getQty();
      	  $discount = number_format($_product->getPrice() - $_product->getFinalPrice(),2);
	  	  $imageUrl = $imageHelper->init($_product, 'small_image')->setImageFile($_product->getData('image'))->getUrl();

			//Code For Webengage
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
          // Code added to check product availability
          $hasStock = true;
	  	  $qty = 0;
            
	  $stockItem = $this->_objectManager->create('\Magento\CatalogInventory\Model\Stock\Item');
		if($_product->getTypeId() == 'bundle'){
			if($_product->getIsSalable()){
				$hasStock = true;
				
			}
		}
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

	   $priceHelper = $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data');

	   $d = array(
			'item_id' => $item->getItemId(),
            'id' => $product->getEntityId(),
            'sku' => $product->getSku(),
            'type' => $product->getTypeId(),
            'name' => $_product->getName(),
			'qty' => $item->getQty(),
            'regular_price' => $priceHelper->currency($_product->getPrice(), true, false),
            'final_price' => $priceHelper->currency($_product->getFinalPrice(), true, false),
            'description' => (!empty($_product->getDescription()) ? $_product->getDescription() : ''),
            'short_description' => (!empty($_product->getShortDescription()) ? $_product->getShortDescription() : ''),
            'image' => $imageUrl,
            'is_salable' => $hasStock,
            'options' => isset($options['info_buyRequest']) ? [$options['info_buyRequest']] : [],
			'remaining_qty' => $qty,
			'number_of_quantity' => $product_quantity,
            'discount' => $discount,
            'department' => $department,
            'category_name' => $category_name,
            'sub_category_name' => $sub_category_name
          );
            
            array_push($data, $d);
        }

        return $data;
    }
    
    public function getQuoteRewardPoints($quote){
    	$points = 0;
    	$points_string = '';
		$purchaseHelper = $this->_objectManager->get('\Mirasvit\Rewards\Helper\Purchase');
		$rewardsData = $this->_objectManager->get('\Mirasvit\Rewards\Helper\Data');
		
		$purchase = $purchaseHelper->getByQuote($quote->getId());
		if ($purchase) {
            $quote = $purchase->getQuote();
            if (strtotime($quote->getUpdatedAt()) < (time() - $purchase->getRefreshPointsTime())) {
                $purchase->updatePoints();
                $purchase = $purchaseHelper->getByQuote($quote->getId()); // load updated purchase
            }
            $points = $purchase
//                ->refreshPointsNumber(true)
                ->getEarnPints();
            if(empty($points)){
            	$points = $purchase->getData('earn_points');
            }
            if ($points) {
                $points_string = $rewardsData->formatPoints($points);
            }
        }

        return ['points' => $points, 'label' => $points_string];
    }
    
    public function getDonations(){
    	$donation_helper = $this->_objectManager->create('MageWorx\Donations\Helper\Data');
		$priceHelper = $this->_objectManager->get('MageWorx\Donations\Helper\Price');
    	$predefinedValues = $donation_helper->getPredefinedValuesDonation();
    	
    	$charityRepository = $this->_objectManager->create('MageWorx\Donations\Model\CharityRepository');
    	$charityCollection = $charityRepository->getListCharity();
        $charityCollection->addFieldToFilter('is_active', 1);
        
        $options = [];

        if (is_array($predefinedValues)) {
            foreach ($predefinedValues as $value) {
                $options[] =
                    [
                        'label' => $priceHelper->getFormatPrice($value),
                        'value' => $value,
                    ];
            }
        }

        /* add custom value */
        if ($donation_helper->isShowDonationCustomAmount()) {
            $options[] =
                [
                    'label' => __('Custom Amount'),
                    'value' => 'custom_donation'
                ];
        }
        
        $donations = [];
        foreach($charityCollection as $collection){
        	
        	$donations[] = [
                    'value' => $collection->getId(),
                    'label' => $collection->getName(),
                    'options' => $options
                ];
        }
        
        return $donations;
    }
}
