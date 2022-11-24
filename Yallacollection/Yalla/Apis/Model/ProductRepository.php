<?php

namespace Yalla\Apis\Model;

use Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\ImageContentValidatorInterface;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Catalog\Model\ProductFactory;
use Mageplaza\AutoRelated\Helper\Rule as HelperRule;
use Mageplaza\AutoRelated\Model\Config\Source\DisplayMode;
use Magento\Framework\Registry;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductRepository implements \Yalla\Apis\Api\ProductRepositoryInterface {
	

    /**
     * @var \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Product[]
     */
    protected $instances = [];

    /**
     * @var Product[]
     */
    protected $instancesById = [];

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceModel;

    /**
     * @var Product\Initialization\Helper\ProductLinks
     */
    protected $linkInitializer;

    /**
     * @var Product\LinkTypeProvider
     */
    protected $linkTypeProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $metadataService;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @deprecated
     * @see \Magento\Catalog\Model\MediaGalleryProcessor
     * @var ImageContentInterfaceFactory
     */
    protected $contentFactory;

    /**
     * @deprecated
     * @see \Magento\Catalog\Model\MediaGalleryProcessor
     * @var ImageProcessorInterface
     */
    protected $imageProcessor;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var ProductRepository\MediaGalleryProcessor
     */
    protected $mediaGalleryProcessor;

    /**
     * @var int
     */
    private $cacheLimit = 0;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $_configurableProTypeModel;
    protected $request;
    protected $apiHelper;
	private $wishlistCollectionFactory;
	
    /**
     * ProductRepository constructor.
     * @param ProductFactory $productFactory
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param ResourceModel\Product $resourceModel
     * @param Product\Initialization\Helper\ProductLinks $linkInitializer
     * @param Product\LinkTypeProvider $linkTypeProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Product\Option\Converter $optionConverter
     * @param ImageContentValidatorInterface $contentValidator
     * @param ImageContentInterfaceFactory $contentFactory
     * @param MimeTypeExtensionMap $mimeTypeExtensionMap
     * @param ImageProcessorInterface $imageProcessor
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param int $cacheLimit [optional]
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
            ProductFactory $productFactory, \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper, \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory, \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder, \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository, \Magento\Catalog\Model\ResourceModel\Product $resourceModel, \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $linkInitializer, \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Api\FilterBuilder $filterBuilder, \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface, \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter, \Magento\Catalog\Model\Product\Option\Converter $optionConverter, ImageContentValidatorInterface $contentValidator, ImageContentInterfaceFactory $contentFactory, MimeTypeExtensionMap $mimeTypeExtensionMap, ImageProcessorInterface $imageProcessor, \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor, ConfigurableProTypeModel $configurableProTypeModel, \Magento\Framework\App\Request\Http $request, \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistCollectionFactory, $cacheLimit = 1000
    ) {
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->initializationHelper = $initializationHelper;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceModel = $resourceModel;
        $this->linkInitializer = $linkInitializer;
        $this->linkTypeProvider = $linkTypeProvider;
        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->filterBuilder = $filterBuilder;
        $this->metadataService = $metadataServiceInterface;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->contentFactory = $contentFactory;
        $this->imageProcessor = $imageProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->cacheLimit = (int) $cacheLimit;
		$this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_configurableProTypeModel = $configurableProTypeModel;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sku, $editMode = false, $storeId = null, $forceReload = false) {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        if (!isset($this->instances[$sku][$cacheKey]) || $forceReload) {
            $product = $this->productFactory->create();

            $productId = $this->resourceModel->getIdBySku($sku);
            if (!$productId) {
                throw new NoSuchEntityException(__('Requested product doesn\'t exist'));
            }
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            }
            $product->load($productId);
            $this->cacheProduct($cacheKey, $product);
        }
        if (!isset($this->instances[$sku])) {
            $sku = trim($sku);
        }
        return $this->instances[$sku][$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function getById($productId, $editMode = false, $storeId = null, $forceReload = false) {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        if (!isset($this->instancesById[$productId][$cacheKey]) || $forceReload) {
            $product = $this->productFactory->create();
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            }
            $product->load($productId);
            if (!$product->getId()) {
                throw new NoSuchEntityException(__('Requested product doesn\'t exist'));
            }
            $this->cacheProduct($cacheKey, $product);
        }

        return $this->instancesById[$productId][$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function productDetails($productId, $customerId = 0, $storeId = null, $forceReload = false) {
    	$editMode = false;
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

	  	$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}

		//Multi store view
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');

		$helper = $objectManager->get('Yalla\Apis\Helper\Data');
        $helper->setStore($lang, $country);
        $currency = $this->request->getParam('currency');
        if($currency) {
        	$this->storeManager->getStore()->setCurrentCurrencyCode($currency);
        }
        //Multi store view

		$storeId = $this->storeManager->getStore()->getId();
		// Product id added in params for auto related products
		$this->request->setParam('productId',$productId);

        $appEmulation = $objectManager->get('Magento\Store\Model\App\Emulation');
        $appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);

        if (!isset($this->instancesById[$productId][$cacheKey]) || $forceReload) {
            $product = $this->productFactory->create();
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            }

            $product->load($productId);
            if (!$product->getId()) {
				echo json_encode(['success' => 'true', 'msg' => 'Product not found.', 'collection' => []]);
				exit;
            }
            $this->cacheProduct($cacheKey, $product);
        }

        $product = $this->instancesById[$productId][$cacheKey];

        $department = $product->getAttributeText('department');
		if(!$department){
		   $department = '';
		}

 		$category_name = $product->getAttributeText('product_category');
		if(!$category_name){
		   $category_name = '';
		}
				
        $sub_category_name = $product->getAttributeText('product_subcategory');
		if(!$sub_category_name){
		   $sub_category_name = '';
		}

        $imageHelper = $objectManager->get('\Magento\Catalog\Helper\Image');
		$priceHelper = $objectManager->get('Magento\Framework\Pricing\Helper\Data');

        $price = $priceHelper->currency($product->getPrice(), true, false);
        $final_price = $priceHelper->currency($product->getFinalPrice(), true, false);
        $finalprice_withoutcurrency = $product->getFinalPrice();
        $discount_price = $product->getPrice() - $product->getFinalPrice();
        $retail_price = $product->getPrice();
        $totalfinalDiscount = '';
        if($product->getPrice() > $product->getFinalPrice()){
            $totalDiscount = $product->getPrice() - $product->getFinalPrice();
            $totalfinalDiscount= ($totalDiscount*100)/$product->getPrice();
            $totalfinalDiscount = number_format($totalfinalDiscount, 2) . '% OFF';
        }
        $data = [];

        $data['id'] = $product->getId();
        $data['sku'] = $product->getSku();
        $data['name'] = $product->getName();
        $data['url'] = $product->getProductUrl();
        $data['attribute_set_id'] = $product->getAttributeSetId();
        $data['discount_percentage'] = $totalfinalDiscount;
        $data['discount_withoutcurrency'] = str_replace(",", "", number_format($discount_price,2));
        $data['price'] = $price;
        $data['retailprice_withoutcurrency'] = str_replace(",", "", number_format($retail_price,2));
        $data['final_price'] = $final_price;
        $data['finalprice_withoutcurrency'] = str_replace(",", "", number_format($finalprice_withoutcurrency, 2));
        $data['size'] = "";
        $data['color'] = "";
        $data['status'] = $product->getStatus();
        $data['type'] = $product->getTypeId();
        $data['brand'] = (!empty($product->getAttributeText('brands')) ? $product->getAttributeText('brands') : '');
        $data['character'] = (!empty($product->getAttributeText('characters')) ? $product->getAttributeText('characters') : '');
		$data['brand_img'] = '';
		$data['brand_id'] = '';
		if($data['brand']) {
			$attribute = $product->getResource()->getAttribute('brands');
			$attribute->setStoreId(\Yalla\Theme\Helper\Data::STORE_ID);
			$brand_name = $attribute->getSource()->getOptionText($product->getData('brands'));
			
			$brandCategory = $objectManager->get('Magento\Catalog\Model\CategoryFactory')->create()->getCollection()
				->addFieldToSelect('name')
				->addAttributeToSelect('brand_logo')
				->addFieldToFilter('name', ['in' => $brand_name])
				->addFieldToFilter('parent_id', ['eq' => 33]);
			if($brandCategory->getSize()){
				$media_url = $this->storeManager
				        ->getStore()
				        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
				foreach($brandCategory as $category){
					$data['brand_id'] = $category->getEntityId();
					$data['brand_img'] = $media_url.'catalog/category/'.$category->getBrandLogo();
				}
			}
		}
		$data['department'] = $department;
        $data['category_name'] = $category_name;
        $data['sub_category_name'] = $sub_category_name;
		
		/** Code to get rewards point **/
        $customer  = $objectManager->get('\Magento\Customer\Model\Customer');
        $finalprice = $product->getFinalPrice();

        $websiteId = $this->storeManager->getStore(true)->getWebsiteId();

        $points = $objectManager->create('Mirasvit\RewardsCatalog\Helper\EarnProductPage')->getProductPagePoints($product, $finalprice, $customer, $websiteId);
        $label = $objectManager->get('Mirasvit\Rewards\Helper\Data')->formatPoints($points);
        if(!empty($points)){
        	$data['rewards'] = "Earn ".$label;
        }
        /** Code to get rewards point **/

        $_product_helper = $objectManager->get('Magento\Catalog\Helper\Image');
        $imageUrl = $_product_helper->init($product, 'small_image')->setImageFile($product->getData('image'))->getUrl();
        $data['image'] = $imageUrl;

        $data['created_at'] = $product->getCreatedAt();
        $data['updated_at'] = $product->getUpdatedAt();
        $data['is_salable'] = $product->getIsSalable();
        $data['description'] = $product->getDescription();
        $data['short_description'] = $product->getShortDescription();
        
        $data['wishlist_item_id'] = "0";
        if($customerId){
		    $wishlist = $this->wishlistCollectionFactory->create()->addCustomerIdFilter($customerId)->addFieldToFilter('product_id', $product->getId());
		    if($wishlist){
		    	foreach($wishlist as $wishlist_item){
		    		$data['wishlist_item_id'] = $wishlist_item->getData('wishlist_item_id');
		    	}
		    }
        }
        $attributes = $product->getAttributes();
        $prod_info = [];

        foreach ($attributes as $attribute) {
            $excludeAttr=[];
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                if (!$product->hasData($attribute->getAttributeCode())) {
                    $value = __('N/A');
                } elseif ((string)$value == '') {
                    $value = __('No');
                }
                
                if (($value instanceof Phrase || is_string($value)) && strlen($value)) {
                    if($attribute->getAttributeCode() != 'brands' && $attribute->getAttributeCode() != 'supplier_name' && $attribute->getAttributeCode() != 'supplier_item_code_number' ){
		    			$value = ucfirst($value);
		    			
		    			if($attribute->getAttributeCode() == 'mw_delivery_time_from') continue;
		    			
						if(isset($prod_info[$attribute->getAttributeCode()])){
							$value = $prod_info[$attribute->getAttributeCode()]['value'].", ".$value;
						}
						if($attribute->getAttributeCode() == 'weight'){
							$value = number_format($value, 3)." ".$helper->getWeightUnit();
						}
		                $prod_info[$attribute->getAttributeCode()] = [
		                    'label' => __($attribute->getStoreLabel()),
		                    'value' => $value,
		                    //'code' => $attribute->getAttributeCode(),
		                ];
                  	}
                }
            }
        }
        $data['more_info'] = $prod_info;
        $shipping_mathod = '';
        $shipping_id = $product->getData('shipping');
        $attr = $product->getResource()->getAttribute('shipping');
		if($attr){
		    if ($attr->usesSource()) {
				$shipping_mathod = $attr->getSource()->getOptionText($shipping_id);         
			}
		}
        if($shipping_mathod == "Express Delivery"){
          $data['shipping_time'] = __('Express Delivery Available (Next Day)');
        }else{
          $data['shipping_time'] = __('Standard Delivery (1-3 Days)');
      	}
        $data['returns'] = __('Returns within 3 days of delivery');


        $data['has_options'] = $product->getHasOptions();
        $data['bundle_options'] = [];

        $inStock = false;
        if ($product->getTypeId() == 'configurable') {
            $options_data = $product->getTypeInstance()->getConfigurableOptions($product);

            foreach ($options_data as $key => $attr) {
                $configurable_options = [];
                $configurable_options = ['attribute_id' => $key];

                foreach ($attr as $p) {
                    if (!isset($configurable_options['type'])) {
                        $configurable_options['type'] = $p['attribute_code'];
                        $configurable_options['attribute_code'] = $p['attribute_code'];
                    }

                    $attributeValues = [$key => $p['value_index']];
                    $assPro = $this->_configurableProTypeModel->getProductByAttributes($attributeValues, $product);

					if($assPro->getStatus() != 1) continue;
					
                    $stockItem = $objectManager->create('\Magento\CatalogInventory\Model\Stock\Item');
                    try {
                        $qty = 0;
                        $productStock = $stockItem->load($assPro->getEntityId(), 'product_id');

                        $qty = $productStock->getQty();
						
                        if ($qty) $inStock = true;
                    } catch (\Exception $e) {
                        
                    }
                    
                    if($qty){
		                $option_swatch = ['label' => $p['default_title'], 'value_id' => $p['value_index']];

		                $configurable_options['attributes'][$p['value_index']] = $option_swatch;
                    }
                }

                // Remove keys from attributes array
                $array_without_key = [];
                foreach ($configurable_options['attributes'] as $attributes) {
                    $array_without_key[] = $attributes;
                }
                $configurable_options['attributes'] = $array_without_key;
                $options[] = $configurable_options;
            }
            $data['configurable_option'] = $options;
        } else if ($product->getTypeId() == 'bundle') {
            if($product->getIsSalable()){
				$inStock = true;
			}
        } else {
            // Code added to check product availability
            $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            
            $tableName = $resource->getTableName('inventory_reservation'); //gives table name with prefix
			//Select Data from table
			$sql = "SELECT sum(`quantity`) as value, sku FROM ".$tableName." where sku='".$product->getSku()."' group by sku";
			$result = $connection->fetchAll($sql);
			$reservation_inv = 0;
			if(count($result)){
				$reservation_inv = abs($result[0]['value']);
				
			}

            $stockItem = $objectManager->create('\Magento\CatalogInventory\Api\StockStateInterface');
			
            try {

					$stock = $stockItem->getStockQty($product->getEntityId(), $websiteId);
					//echo $stock;
					if($stock>0){
						$inStock = true;
					}	
				/*
                $qty = 0;
                $productStock = $stockItem->load($product->getEntityId(), 'product_id');

                $qty = $productStock->getQty();
				echo $qty;
                if ($qty && $reservation_inv < $qty)
                    $inStock = true; */
				
            } catch (\Exception $e) {
                
            }
            // Code added to check product availability
        }

        $data['is_salable'] = $inStock;
        $mediaGalleryEntries = $product->getMediaGalleryEntries();
        $data['images'] = [];
        foreach ($mediaGalleryEntries as $row) {
            if ($row->getMediaType() == 'image' && !$row->getData('disabled')) {
                $image_url = $imageHelper->init($product, 'image')->setImageFile($row->getFile())->getUrl();
                $data['images'][] = $image_url;
            }
        }

		// Reviews and rating
		$reviewFactory = $objectManager->create('Magento\Review\Model\Review');
		$reviewFactory->getEntitySummary($product, $storeId);

		$ratingSummary = $product->getRatingSummary()->getRatingSummary();
		$reviewsCount = $product->getRatingSummary()->getReviewsCount();
		$data['rating'] = 0;
		$data['reviews_count'] = 0;
		$data['reviews'] = [];
		if($ratingSummary){
			$data['rating'] = $ratingSummary;
			$data['reviews_count'] = $reviewsCount;
		}
		if($reviewsCount){
			$reviewsModel = $objectManager->get("Magento\Review\Model\ResourceModel\Review\CollectionFactory");
			$reviews = $reviewsModel->create()
			->addStatusFilter(
				\Magento\Review\Model\Review::STATUS_APPROVED
			)->addEntityFilter(
				'product',
				$product->getId()
			)->setPageSize(50)->setCurPage(1)->setDateOrder();
			$product_reviews = $reviews->getData();
			foreach($product_reviews as $product_review){
				$data['reviews'][] = ['review_id' => $product_review['review_id'], 'title' => $product_review['title'],
				 'detail' => $product_review['detail'], 'nickname' => $product_review['nickname'], 'review_date' => $product_review['created_at']];
			}
		}

        // Upsell products
        $upsell_ids = [];
        $bundleIds = [];
        if ($product->getTypeId() != 'bundle') {
            $resource = $objectManager->create('Magento\Bundle\Model\ResourceModel\Selection');
            $bundleIds = $resource->getParentIdsByChild($product->getId());
        }
        $upSellProducts = $product->getUpSellProducts();
        foreach ($upSellProducts as $upSellProduct) {
            $upsell_ids[] = $upSellProduct->getEntityId();
        }

        $upsell_ids = array_merge($upsell_ids, $bundleIds);

        $upsell_list = array();
        if (count($upsell_ids)) {
            foreach ($upsell_ids as $upsell_id) {
                $_product = $objectManager->create('Magento\Catalog\Model\Product')->load($upsell_id);
                if ($_product->getData('visibility') == 1) {
                    continue;
                }
                $product_info = $this->getProductData($_product);
                if (count($product_info))
                    $upsell_list[] = $product_info;
            }
        }
        $data['upsell'] = $upsell_list;

        // Related products
        $data['related'] = $this->getRelatedProducts($product);
		//$data['combos'] = $this->getComboProducts($product);
		$data['combos'] = array();

		// Auto related products
		$helper_autorelated = $objectManager->create('Mageplaza\AutoRelated\Helper\Rule');
		$autorelated_block = $objectManager->create('Mageplaza\AutoRelated\Block\Product\Block');
		$helper_autorelated->setData('type','product');
	    $helper_autorelated->setData('entity_id', $product->getId());
	    $collection = [];

   		$activerules = $helper_autorelated->getActiveRulesByMode(DisplayMode::TYPE_BLOCK);	    
		foreach ($activerules as $rule) {
			$autorelated_block = $autorelated_block->setRule($rule);
		
            $rule_id = $rule->getId();
            $rule_title = $autorelated_block->getTitleBlock();
			$autorelated_collection = $autorelated_block->getProductCollection();
			//$collection = $autorelated_collections->getData();
			$i=0;
			$products = [];
			foreach ($autorelated_collection as $product){
				$products[] = $this->getProductData($product);
				$i++;
				if($i==8){
					break;
				}
			}
			if(count($products)){
				$collection[] = array('title' => $rule_title, 'products' => $products);
			}
            $rule->getResource()->updateImpression($rule->getId());
		
        }

		$activerules = $helper_autorelated->getActiveRulesByMode(DisplayMode::TYPE_AJAX);
		foreach ($activerules as $rule) {
			$autorelated_block = $autorelated_block->setRule($rule);
		
            $rule_id = $rule->getId();
            $rule_title = $autorelated_block->getTitleBlock();
			$autorelated_collection = $autorelated_block->getProductCollection();
			//$collection = $autorelated_collections->getData();
			$i=0;
			$products = [];
			foreach ($autorelated_collection as $product){
				$products[] = $this->getProductData($product);
				$i++;
				if($i==8){
					break;
				}
			}
			if(count($products)){
				$collection[] = array('title' => $rule_title, 'products' => $products);
			}
            $rule->getResource()->updateImpression($rule->getId());
		
        }

		$data['autorelated'] = $collection;
	
	

        echo json_encode(['success' => 'true', 'msg' => '', 'collection' => $data]);
		exit;
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data) {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }

        return md5(serialize($serializeData));
    }

    /**
     * Add product to internal cache and truncate cache if it has more than cacheLimit elements.
     *
     * @param string $cacheKey
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return void
     */
    private function cacheProduct($cacheKey, \Magento\Catalog\Api\Data\ProductInterface $product) {
        $this->instancesById[$product->getId()][$cacheKey] = $product;
        $this->instances[$product->getSku()][$cacheKey] = $product;

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, $offset, null, true);
            $this->instances = array_slice($this->instances, $offset, null, true);
        }
    }

    /**
     * Merge data from DB and updates from request
     *
     * @param array $productData
     * @param bool $createNew
     * @return \Magento\Catalog\Api\Data\ProductInterface|Product
     * @throws NoSuchEntityException
     */
    protected function initializeProductData(array $productData, $createNew) {
        unset($productData['media_gallery']);
        if ($createNew) {
            $product = $this->productFactory->create();
            if (isset($productData['price']) && !isset($productData['product_type'])) {
                $product->setTypeId(Product\Type::TYPE_SIMPLE);
            }
            if ($this->storeManager->hasSingleStore()) {
                $product->setWebsiteIds([$this->storeManager->getStore(true)->getWebsiteId()]);
            }
        } else {
            if (!empty($productData['id'])) {
                unset($this->instancesById[$productData['id']]);
                $product = $this->getById($productData['id']);
            } else {
                unset($this->instances[$productData['sku']]);
                $product = $this->get($productData['sku']);
            }
        }

        foreach ($productData as $key => $value) {
            $product->setData($key, $value);
        }
        $this->assignProductToWebsites($product, $createNew);

        return $product;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $createNew
     * @return void
     */
    private function assignProductToWebsites(\Magento\Catalog\Model\Product $product, $createNew) {
        $websiteIds = $product->getWebsiteIds();

        if (!$this->storeManager->hasSingleStore()) {
            $websiteIds = array_unique(
                    array_merge(
                            $websiteIds, [$this->storeManager->getStore()->getWebsiteId()]
                    )
            );
        }

        if ($createNew && $this->storeManager->getStore(true)->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
            $websiteIds = array_keys($this->storeManager->getWebsites());
        }

        $product->setWebsiteIds($websiteIds);
    }

    /**
     * Process product links, creating new links, updating and deleting existing links
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface[] $newLinks
     * @return $this
     * @throws NoSuchEntityException
     */
    private function processLinks(\Magento\Catalog\Api\Data\ProductInterface $product, $newLinks) {
        if ($newLinks === null) {
            // If product links were not specified, don't do anything
            return $this;
        }

        // Clear all existing product links and then set the ones we want
        $linkTypes = $this->linkTypeProvider->getLinkTypes();
        foreach (array_keys($linkTypes) as $typeName) {
            $this->linkInitializer->initializeLinks($product, [$typeName => []]);
        }

        // Set each linktype info
        if (!empty($newLinks)) {
            $productLinks = [];
            foreach ($newLinks as $link) {
                $productLinks[$link->getLinkType()][] = $link;
            }

            foreach ($productLinks as $type => $linksByType) {
                $assignedSkuList = [];
                /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $link */
                foreach ($linksByType as $link) {
                    $assignedSkuList[] = $link->getLinkedProductSku();
                }
                $linkedProductIds = $this->resourceModel->getProductsIdsBySkus($assignedSkuList);

                $linksToInitialize = [];
                foreach ($linksByType as $link) {
                    $linkDataArray = $this->extensibleDataObjectConverter
                            ->toNestedArray($link, [], \Magento\Catalog\Api\Data\ProductLinkInterface::class);
                    $linkedSku = $link->getLinkedProductSku();
                    if (!isset($linkedProductIds[$linkedSku])) {
                        throw new NoSuchEntityException(
                                __('Product with SKU "%1" does not exist', $linkedSku)
                        );
                    }
                    $linkDataArray['product_id'] = $linkedProductIds[$linkedSku];
                    $linksToInitialize[$linkedProductIds[$linkedSku]] = $linkDataArray;
                }

                $this->linkInitializer->initializeLinks($product, [$type => $linksToInitialize]);
            }
        }

        $product->setProductLinks($newLinks);
        return $this;
    }

    public function getProductsList($categoryId, $filters = []) {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        $collection->addAttributeToSelect('*');

        $postData = file_get_contents("php://input");
        if (!empty($postData)) {
            $request = json_decode($postData, true);
        }

        $searchQuery = '';
        if (isset($request['searchQuery'])) {
            $searchQuery = $request['searchQuery'];
            if (!empty($searchQuery)) {
                $collection->addAttributeToFilter('name', array('like' => '%' . $searchQuery . '%'));
            }
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($categoryId) {
            $category = $objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
            $stockFilter = $objectManager->create('\Magento\CatalogInventory\Helper\Stock');
            $parent_id = $category->getData('parent_id');
            $collection->addCategoryFilter($category);
			$collection->addAttributeToFilter('is_saleable', ['eq' => 1]);
			$collection->addFieldToFilter('status', array('eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED));
			$stockFilter->addInStockFilterToCollection($collection);
        }

        $collection = $collection->addPriceData(1, 1);

        // $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

		foreach ($filters as $code => $value) {
            if (!empty($value) && $code != 77 && $code != "price") {
                $attribute_option_ids = explode(",", $value);
                $collection->addAttributeToFilter($code, array('in' => $attribute_option_ids));
            }
        }


        $tableAlias = 'catalog_eav';
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id"
        );

        $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');

        $priceCondition = '';
        foreach ($filters as $code => $value) {
            if (!empty($value) && $code == 77) {
                $priceArray = explode(',', $value);
                foreach ($priceArray as $price_range) {
                    $price = explode('-', $price_range);
                    if (!isset($price[0]) || !isset($price[1]) || (empty($price[0]) && empty($price[1]))) {
                        continue;
                    }
                    if (empty($price[0])) {
                        $condition = 'lteq';
                        $val = $price[1];
                        if (!empty($priceCondition)) {
                            $priceCondition .= ' OR ';
                        }
                        $priceCondition .= "price_index.min_price <= " . $val;
                    }
                    if (empty($price[1])) {
                        $condition = 'gteq';
                        $val = $price[0];
                        if (!empty($priceCondition)) {
                            $priceCondition .= ' OR ';
                        }
                        $priceCondition .= "price_index.max_price >= " . $val;
                    }
                    if (!empty($price[0]) && !empty($price[1])) {
                        if (!empty($priceCondition)) {
                            $priceCondition .= ' OR ';
                        }
                        $priceCondition .= "(price_index.min_price >= " . $price[0] . " && price_index.max_price <=" . $price[1] . ")";
                    }
                }
            }
        }

		$collection->setFlag('has_stock_status_filter', true);
		$collection->getSelect()->joinLeft( array( 'stock_item' => 'cataloginventory_stock_item' ), 'stock_item.product_id = e.entity_id', array("qty","backorders"));
		
		$subquery = new \Zend_Db_Expr('(SELECT sum(`quantity`) as value, sku FROM `inventory_reservation`group by sku)');
		$collection->getSelect()->joinLeft( array( 't' => $subquery ), 'e.sku = t.sku', array());

		$collection->getSelect()->where("abs(t.value) is NULL OR (abs(t.value) < stock_item.qty)");
		
		$collection->getSelect()->where("stock_item.backorders != 300");
		
        if (!empty($priceCondition)) {
            $collection->getSelect()->where("(" . $priceCondition . ")");
        }

        $attribute_conditions = '';
        foreach ($filters as $code => $value) {
            if (!empty($value) && $code != 77) {
                if (!empty($attribute_conditions)) {
                    $attribute_conditions .= ' OR ';
                }
                $attribute_conditions .= "({$tableAlias}.attribute_id = '{$code}' AND {$tableAlias}.value in ({$value}))";
            }
        }

		$sort_order = 'asc';
        $sort_attr = 'position';
        if (isset($request['sort'])) {
            if ($request['sort'] == 'price_desc') {
                $sort_attr = 'price';
                $sort_order = 'desc';
            }
            if ($request['sort'] == 'price_asc') {
                $sort_attr = 'price';
                $sort_order = 'asc';
            }
            if ($request['sort'] == 'new') {
                $sort_attr = 'created_at';
                $sort_order = 'desc';
            }
        }
        $collection->getSelect()->order($sort_attr." ".$sort_order);
        //$collection->addAttributeToSort($sort_attr, $sort_order);
        
        $collection->getSelect()->group('e.entity_id');
        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getList() {

		 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

			$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

			$auth = $api_auth->Auth(); 

			if($auth['success'] == "false"){
				echo json_encode($auth);
				exit;
			}
        $postData = file_get_contents("php://input");
        if (!empty($postData)) {
            $request = json_decode($postData, true);
        }

        $pageSize = 10;
        if (isset($request['pageSize'])) {
            $pageSize = (int) $request['pageSize'];
        }

        $page = 0;
        if (isset($request['page'])) {
            $page = (int) $request['page'];
            $page = ($page - 1) * $pageSize;
        }

        $categoryId = 0;
        if (isset($request['category'])) {
            $categoryId = (int) $request['category'];
        }

        try {

           

            $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
			// Multi store view
		    $lang = $this->request->getParam('lang');
			$country = $this->request->getParam('country');
			$apiHelper = $objectManager->get('Yalla\Apis\Helper\Data');
			$apiHelper->setStore($lang, $country);
		    $currency = $this->request->getParam('currency');
			//var_dump($currency);
		    if($currency) {
		    	$storeManager->getStore()->setCurrentCurrencyCode($currency);
		    }
			// Multi store view
	    
            $lang = $this->request->getParam('lang');
            if (!empty($lang)) {
                $this->apiHelper = $objectManager->get('Yalla\Apis\Helper\Data');
                $this->apiHelper->setStore($lang,$country);
            }

            $finalFilters = $this->getAllFilters($categoryId);
            $sorting = $this->getSortOrders();

            $attributes = [];
            if (isset($request['attributes'])) {
                $attributes = is_array($request['attributes']) ? $request['attributes'] : [];
            }
            $productdata = $this->getProductsList($categoryId, $attributes);
			// echo json_encode($productdata->count()); exit;

            $_productModel = clone $productdata;
            $_productModel->getSelect()->limit($pageSize, $page);


            $total_products = $productdata->count();

            $total_page_count = 1;
            if ($total_products >= $pageSize) {
                $remainder = $total_products % $pageSize;
                if ($remainder > 0) {
                    $total_page_count = ((int) ($total_products / $pageSize)) + 1;
                } else {
                    $total_page_count = ((int) ($total_products / $pageSize));
                }
            }
			$websiteId = $this->storeManager->getStore(true)->getWebsiteId();
			$priceHelper = $objectManager->get('Magento\Framework\Pricing\Helper\Data');
			$_product_helper = $objectManager->get('Magento\Catalog\Helper\Image');
        
            $response = [];
            foreach ($_productModel as $product) {
				
				$productDetails = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getEntityId());
				//$inStock = true;
				if (!$productDetails->getIsSalable())
				continue;

            	$price = $product->getPrice();
        		$final_price = $product->getFinalPrice();
        		$imageUrl = $_product_helper->init($product, 'category_page_list')->setImageFile($product->getData('image'))->resize(400,400)->getUrl();
        		
        		$qty = 0;
				if ($product->getTypeId() == 'simple') {
				   $stockItem = $objectManager->create('\Magento\CatalogInventory\Model\Stock\Item');
					//$stockItem = $objectManager->create('\Magento\CatalogInventory\Api\StockStateInterface');
				    try {
						
							/*$stock = $stockItem->getStockQty($product->getEntityId(), $websiteId);
							//echo $stock;
							if($stock>0){
								$inStock = true;

							}
							else{
								$inStock = false;
								break;
							}*/
				        //$productStock = $stockItem->load($product->getEntityId(), 'product_id');

				       // $qty = $productStock->getQty();
						//echo $qty;
						
						//$inStock = true;
						if ($productDetails->getIsSalable()){
							$inStock = true;
						}
				       
				    } catch (\Exception $e) {
				        $inStock = false;
				    }
				} else {
				    $inStock = true;
				}
				
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
				
            	$response[] = array(
				    'id' => $product->getEntityId(),
				    'sku' => $product->getSku(),
				    'type' => $product->getTypeId(),
				    'name' => $product->getName(),
				    'regular_price_value' => (string) $price,
				    'final_price_value' => (string) $final_price,
				    'regular_price' => $priceHelper->currency($price, true, false),
				    'final_price' => $priceHelper->currency($final_price, true, false),
				    'description' => (!empty($product->getDescription()) ? $product->getDescription() : ''),
				    'short_description' => (!empty($product->getShortDescription()) ? $product->getShortDescription() : ''),
				    'image' => $imageUrl."?tr=h-350,w-350",
				    'is_salable' => $inStock,
				    'trending_label' => $trending_label,
				    'exclusive_label' => $exclusive_label,
				    'featured_label' => $featured_label
				);
                //$response[] = $this->getProductData($product);
            }
            echo json_encode(['success' => 'true', 'msg' => 'Data found', 'collection' => $response, 'filters' => $finalFilters, 'sorting' => $sorting, "total_products" => $total_products, "total_page_count" => $total_page_count]);
	    exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => $e->getMessage()]);
	    exit;
        }
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @deprecated 101.1.0
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
            \Magento\Framework\Api\Search\FilterGroup $filterGroup, Collection $collection
    ) {
        $fields = [];
        $categoryFilter = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ?: 'eq';

            if ($filter->getField() == 'category_id') {
                $categoryFilter[$conditionType][] = $filter->getValue();
                continue;
            }
            $fields[] = ['attribute' => $filter->getField(), $conditionType => $filter->getValue()];
        }

        if ($categoryFilter) {
            $collection->addCategoriesFilter($categoryFilter);
        }

        if ($fields) {
            $collection->addFieldToFilter($fields);
        }
    }

    /**
     * Clean internal product cache
     *
     * @return void
     */
    public function cleanCache() {
        $this->instances = null;
        $this->instancesById = null;
    }

    /**
     * @return ProductRepository\MediaGalleryProcessor
     */
    private function getMediaGalleryProcessor() {
        if (null === $this->mediaGalleryProcessor) {
            $this->mediaGalleryProcessor = \Magento\Framework\App\ObjectManager::getInstance()
                    ->get(ProductRepository\MediaGalleryProcessor::class);
        }
        return $this->mediaGalleryProcessor;
    }

    public function getAllFilters($categoryId = 0) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $filterableAttributes = $objectManager->get(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList::class);

        $appState = $objectManager->get(\Magento\Framework\App\State::class);
        $layerResolver = $objectManager->get(\Magento\Catalog\Model\Layer\Resolver::class);
        $filterList = $objectManager->create(
                \Magento\Catalog\Model\Layer\FilterList::class, [
            'filterableAttributes' => $filterableAttributes
                ]
        );

        $layer = $layerResolver->get();
        if ($categoryId) {
            $layer->setCurrentCategory($categoryId);
        }
        $filters = $filterList->getFilters($layer);

        $finalFilters = [];
        foreach ($filters as $filter) {
            if ($filter->getItemsCount()) {
                $name = strip_tags((string) $filter->getName());
                $filter_arr = ['name' => $name];

		if($filter_arr['name'] == 'Category') continue;

                $filter_arr['attr_id'] = '0';
                $filter_data = $filter->getData();
                if (isset($filter_data['attribute_model'])) { // Set code if filter is an attribute e.g. color, size etc
                    $filter_arr['code'] = $filter->getAttributeModel()->getData('attribute_code');
                    $filter_arr['attr_id'] = (string) $filter->getAttributeModel()->getData('attribute_id');
                }

                // Find attribute option values
                foreach ($filter->getItems() as $item) {
                    $filter_arr['options'][] = array('id' => $item->getValue(), 'option_name' => html_entity_decode(strip_tags((string) $item->getLabel())));
                }

                $finalFilters[] = $filter_arr;
            }
        }
        return $finalFilters;
    }

    public function getSortOrders() {
        return [
            ['sort_value' => 'price_desc', 'sort_name' => __('Price - High to Low')],
            ['sort_value' => 'price_asc', 'sort_name' => __('Price - Low to High')],
            ['sort_value' => 'new', 'sort_name' => __('Relevance')]
        ];
    }

    public function getCollection($filters = [], $categoryId = 0) {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        $collection->addAttributeToSelect('*');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($categoryId) {
            $category = $objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
            $collection->addCategoryFilter($category);
        }

        $collection = $collection->addPriceData(1, 1);

        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');


        $tableAlias = 'catalog_eav';
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id"
        );

        $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
        $collection->getSelect()->join(
                array($tableAlias => $resource->getTableName('catalog_product_index_eav')), implode(' AND ', $conditions), array()
        );

        $priceCondition = '';
        foreach ($filters as $code => $value) {
            if (!empty($value) && $code == 77) {
                $priceArray = explode(',', $value);
                foreach ($priceArray as $price_range) {
                    $price = explode('-', $price_range);
                    if (!isset($price[0]) || !isset($price[1]) || (empty($price[0]) && empty($price[1]))) {
                        continue;
                    }
                    if (empty($price[0])) {
                        $condition = 'lteq';
                        $val = $price[1];
                        if (!empty($priceCondition)) {
                            $priceCondition .= ' OR ';
                        }
                        $priceCondition .= "price_index.min_price <= " . $val;
                    }
                    if (empty($price[1])) {
                        $condition = 'gteq';
                        $val = $price[0];
                        if (!empty($priceCondition)) {
                            $priceCondition .= ' OR ';
                        }
                        $priceCondition .= "price_index.max_price >= " . $val;
                    }
                    if (!empty($price[0]) && !empty($price[1])) {
                        if (!empty($priceCondition)) {
                            $priceCondition .= ' OR ';
                        }
                        $priceCondition .= "(price_index.min_price >= " . $price[0] . " && price_index.max_price <=" . $price[1] . ")";
                    }
                }
            }
        }

        if (!empty($priceCondition)) {
            $collection->getSelect()->where("(" . $priceCondition . ")");
        }

        $attribute_conditions = '';
        foreach ($filters as $code => $value) {
            if (!empty($value) && $code != 77) {
                if (!empty($attribute_conditions)) {
                    $attribute_conditions .= ' OR ';
                }
                $attribute_conditions .= "({$tableAlias}.attribute_id = {$code} AND {$tableAlias}.value in ({$value}))";
            }
        }

        if (!empty($attribute_conditions)) {
            $collection->getSelect()->where("(" . $attribute_conditions . ")");
        }
        return $collection;
    }

    public function getComboProducts($product) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

	//$block = $objectManager->create('\Magedelight\Bundlediscount\Block\Bundle');
	$bundlediscount = $objectManager->create('\Magedelight\Bundlediscount\Model\Bundlediscount');
	$helper = $objectManager->create('\Magedelight\Bundlediscount\Helper\Data');
	$bundleItems = $objectManager->create('\Magedelight\Bundlediscount\Model\Bundleitems');

	$bundleCollection = $bundlediscount->getBundlesByProduct($product);
	$displayOptions = $helper->displayOption();

	$priceHelper = $objectManager->get('Magento\Framework\Pricing\Helper\Data');

	$combos = [];
        if ($displayOptions == 'both') {
            $otherBundles = $bundleItems->getCollection()
                            ->addFieldToSelect('bundle_id')
                            ->addFieldToFilter('product_id', ['eq' => $product->getId()]);
            $otherIds = $otherBundles->getColumnValues('bundle_id');

            if (count($otherIds) > 0) {
                $bundleCollection = $bundlediscount->getBundleObjects($otherIds, $bundleCollection);
            }
	    if($bundleCollection){

		foreach ($bundleCollection as $bundle){
		     try{
				$combo_salable = true;
				
				$totals = $bundlediscount->calculateDiscountAmount($bundle);

				$discount_label = $totals['discount_label']. ' discount';
				$discount_amount = '';
				if ($bundle->getDiscountType() == '0' || (!$bundle->hasOptions() && !$bundle->hasCustomOptions())) {
					$discount_amount = $priceHelper->currency($totals['discount_amount'], true, false);
				}

				$combos[$bundle->getId()] = array(
					'id' => $bundle->getId(),
					'name' => $bundle->getName(),
					'regular_price' => strip_tags($priceHelper->currency($totals['total_amount'])),
					'final_price' => strip_tags($priceHelper->currency($totals['final_amount'])),
					'discount_label' => $discount_label,
					'discount_amount' => $discount_amount,
				);

				$combos[$bundle->getId()]['items'][] = array(
					'id' => $bundle->getProductId(),
					'sku' => $bundle->getProductSku(),
					'type' => $bundle->getTypeId(),
					'name' => $bundle->getProductName(),
					'regular_price_value' => (string) $bundle->getProductPrice(),
					'final_price_value' => (string) $bundle->getProductPrice(),
					'regular_price' => $priceHelper->currency($bundle->getProductPrice(), true, false),
					'final_price' => $priceHelper->currency($bundle->getProductPrice(), true, false),
					'description' => '',
					'short_description' => '',
					'image' => $bundle->getImageUrl(),
					'is_salable' => ($bundle->getIsSalable()) ? true : false
				);
				if(!$bundle->getIsSalable()){
					$combo_salable = false;
				}

				$_selections = $bundle->getSelections();
				foreach ($_selections as $_selection){
					$combos[$bundle->getId()]['items'][] = array(
						'id' => $_selection->getProductId(),
						'sku' => $_selection->getSku(),
						'type' => $_selection->getTypeId(),
						'name' => $_selection->getName(),
						'regular_price_value' => (string) $_selection->getPrice(),
						'final_price_value' => (string) $_selection->getPrice(),
						'regular_price' => $priceHelper->currency($_selection->getPrice(), true, false),
						'final_price' => $priceHelper->currency($_selection->getPrice(), true, false),
						'description' => '',
						'short_description' => '',
						'image' => $_selection->getImageUrl(),
						'is_salable' => ($_selection->getIsSalable()) ? true : false
					);
					if(!$_selection->getIsSalable()){
						$combo_salable = false;
					}
				}
				$combos[$bundle->getId()]['is_salable'] = $combo_salable;
		    }catch(\Exception $e){}
	    	}
	    }
	    return array_values($combos);
        }

	return array();
    }

    public function getRelatedProducts($product) {
        $related_ids = [];

        // Get related products
        $relatedProducts = $product->getRelatedProducts();
        foreach ($relatedProducts as $relatedProduct) {
            $related_ids[] = $relatedProduct->getEntityId();
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $related_list = array();

        // Prepare product array
        if (count($related_ids)) {
            foreach ($related_ids as $related_id) {
                $_product = $objectManager->create('Magento\Catalog\Model\Product')->load($related_id);

                if ($_product->getData('status') != 1) {
                    continue;
                }


                $related_list[] = $this->getProductData($_product);
            }
        }

        return $related_list;
    }

    public function getProductData($product, $cart_qty = 0) {
        $inStock = true;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_product_helper = $objectManager->get('Magento\Catalog\Helper\Image');
		$priceHelper = $objectManager->get('Magento\Framework\Pricing\Helper\Data');

        $_product = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getEntityId());

		$brand = $_product->getAttributeText('brands');
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

        if ($_product->getData('status') != 1) {
            return [];
        }

        $productId = $product->getEntityId();
        $sku = $product->getSku();
        $type = $product->getTypeId();

        
//        $price = $product->getPriceInfo()->getPrice('regular_price')->getValue();
//        $final_price = $product->getPriceInfo()->getPrice('final_price')->getValue();
        $price = $product->getPrice();
        $final_price = $product->getFinalPrice();
		$discount = $price - $final_price;
        $qty = 0;
		
        if ($product->getTypeId() == 'simple') {
            $stockItem = $objectManager->create('\Magento\CatalogInventory\Model\Stock\Item');
            try {
                $productStock = $stockItem->load($product->getEntityId(), 'product_id');

                $qty = $productStock->getQty();
                if ($qty)
                    $inStock = true;
                else
                    $inStock = false;
            } catch (\Exception $e) {
                $inStock = false;
            }
        } else {
            $inStock = true;
        }
        
        $imageUrl = $_product_helper->init($_product, 'category_page_list')->setImageFile($_product->getData('image'))->resize(400,400)->getUrl();

		$product_tag = $_product->getTags();
					
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
        
        $product_data = array(
            'id' => $productId,
            'sku' => $sku,
            'type' => $type,
            'name' => $product->getName(),
            'regular_price_value' => (string) $price,
            'final_price_value' => (string) $final_price,
            'regular_price' => $priceHelper->currency($price, true, false),
            'final_price' => $priceHelper->currency($final_price, true, false),
            'description' => (!empty($product->getDescription()) ? $product->getDescription() : ''),
            'short_description' => (!empty($product->getShortDescription()) ? $product->getShortDescription() : ''),
            'image' => $imageUrl,
            'is_salable' => $inStock,
            'trending_label' => $trending_label,
            'exclusive_label' => $exclusive_label,
            'featured_label' => $featured_label,
            'brand' => $brand,
            'character' => $character,
            'discount' => number_format($discount,2),
            'size' => "",
            'color' => "",
            'department' => $department,
            'category_name' => $category_name,
            'sub_category_name' => $sub_category_name
        );

        return $product_data;
    }
    
    public function submitReview($productId){
	
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
	$postData = file_get_contents("php://input");
        if (!empty($postData)) {
            $request = json_decode($postData, true);
        }

        if ($error = $this->validate($request)) {
            echo json_encode(array('success' => 'false','msg' => $error));
            exit;
        }

	$rating = $request['rating'];
	$nickname = $request['nickname'];
	$title = $request['title'];
	$detail = $request['detail'];

	try{
	        
		$reviewFactory = $objectManager->get('Magento\Review\Model\ReviewFactory');
		$ratingFactory = $objectManager->get('Magento\Review\Model\RatingFactory');
         //Multi store view
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
		$apiHelper = $objectManager->get('Yalla\Apis\Helper\Data');
        $apiHelper->setStore($lang, $country);
        //Multi store view

		$reviewData['ratings'][1] = $rating;
		$reviewData['nickname'] = $nickname;
		$reviewData['title'] = $title;
		$reviewData['detail'] = $detail;
		$review = $reviewFactory->create()->setData($reviewData);
		$review->unsetData('review_id');
		$review->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
		    ->setEntityPkValue($productId)
		    ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)//By default set approved
		    ->setStoreId($this->storeManager->getStore()->getId())
		    ->setStores([$this->storeManager->getStore()->getId()])
		    ->save();
	 
		foreach ($reviewData['ratings'] as $ratingId => $optionId) {
		    $ratingFactory->create()
		        ->setRatingId($ratingId)
		        ->setReviewId($review->getId())
		        ->addOptionVote($optionId, $productId);
		}
	 
		$review->aggregate();
                echo json_encode(['success' => 'true', 'msg' => 'Your review has been submitted successfully.']);
                exit;
	} catch (\Exception $e) {
            echo json_encode(['success' => 'false', 'msg' => $e->getMessage()]);
	    exit;
        }
    }

    private function validate($data) {
        $error = '';
        if (!isset($data['rating']) || empty($data['rating'])) {
            $error = 'Please give rating to product.';
        } else if (!isset($data['nickname']) || empty($data['nickname'])) {
            $error = 'Missing mandatory field.';
        } else if (!isset($data['title']) || empty($data['title'])) {
            $error = 'Please provide title of review.';
        } else if (!isset($data['detail']) || empty($data['detail'])) {
            $error = 'Comment is mandatory.';
        }

        return $error;
    }

}
