<?php

/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Yalla\Apis\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class CategoryManagement implements \Yalla\Apis\Api\CategoryManagementInterface {

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\Category\Tree
     */
    protected $categoryTree;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoriesFactory;
    protected $request;
    protected $_objectManager;
    protected $storeManager;
    
    /**
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Model\Category\Tree $categoryTree
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoriesFactory
     */
    public function __construct(
            \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
            \Magento\Catalog\Model\Category\Tree $categoryTree,
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoriesFactory,
            \Magento\Framework\App\Request\Http $request
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryTree = $categoryTree;
        $this->categoriesFactory = $categoriesFactory;
        $this->request = $request;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    }

    /**
     * {@inheritdoc}
     */
    public function getTree($rootCategoryId = null, $depth = null) {

        $category = null;
        if ($rootCategoryId !== null) {
            /** @var \Yalla\Apis\Model\Category $category */
            $category = $this->categoryRepository->get($rootCategoryId);
        } elseif ($this->isAdminStore()) {
            $category = $this->getTopLevelCategory();
        }
        //$result = $this->categoryTree->getTree($this->categoryTree->getRootNode($category), $depth);
        $result = $this->getCategories($category);
        return $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTree2($rootCategoryId = null, $depth = null) {

		$api_auth = $this->_objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        $lang = $this->request->getParam('lang');
	    $country = $this->request->getParam('country');
	    $apiHelper = $this->_objectManager->get('Yalla\Apis\Helper\Data');
	    $apiHelper->setStore($lang, $country);
        $category = null;
        if ($rootCategoryId !== null) {
            /** @var \Yalla\Apis\Model\Category $category */
            $category = $this->categoryRepository->get($rootCategoryId);
        } elseif ($this->isAdminStore()) {
            $category = $this->getTopLevelCategory();
        }
        //$result = $this->categoryTree->getTree($this->categoryTree->getRootNode($category), $depth);
        $result = $this->getCategories($category);
        echo json_encode($result);die;
    }

    /**
     * Check is request use default scope
     *
     * @return bool
     */
    private function isAdminStore() {
        return $this->getScopeResolver()->getScope()->getCode() == \Magento\Store\Model\Store::ADMIN_CODE;
    }

    /**
     * Get store manager for operations with admin code
     *
     * @return \Magento\Framework\App\ScopeResolverInterface
     */
    private function getScopeResolver() {
        if ($this->scopeResolver == null) {
            $this->scopeResolver = \Magento\Framework\App\ObjectManager::getInstance()
                    ->get(\Magento\Framework\App\ScopeResolverInterface::class);
        }

        return $this->scopeResolver;
    }

    /**
     * Get top level hidden root category
     *
     * @return \Yalla\Apis\Model\Category
     */
    private function getTopLevelCategory() {
        $categoriesCollection = $this->categoriesFactory->create();
        return $categoriesCollection->addFilter('level', ['eq' => 0])->getFirstItem();
    }

    /**
     * {@inheritdoc}
     */
    public function move($categoryId, $parentId, $afterId = null) {
        $model = $this->categoryRepository->get($categoryId);
        $parentCategory = $this->categoryRepository->get($parentId);

        if ($parentCategory->hasChildren()) {
            $parentChildren = $parentCategory->getChildren();
            $categoryIds = explode(',', $parentChildren);
            $lastId = array_pop($categoryIds);
            $afterId = ($afterId === null || $afterId > $lastId) ? $lastId : $afterId;
        }

        if (strpos($parentCategory->getPath(), $model->getPath()) === 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                    __('Operation do not allow to move a parent category to any of children category')
            );
        }
        try {
            $model->move($parentId, $afterId);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Could not move category'));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount() {
        $categories = $this->categoriesFactory->create();
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
        $categories->addAttributeToFilter('parent_id', ['gt' => 0]);
        return $categories->getSize();
    }

    public function getObjectManager() {
        if (empty($this->_objectManager)) {
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }

        return $this->_objectManager;
    }

    public function getChild($category) {
        $objectManager = $this->getObjectManager();
        $collection = $category->getCollection();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection->addAttributeToSelect(
                'url_key'
        )->addAttributeToSelect(
                'name'
        )->addAttributeToSelect(
                'all_children'
        )->addAttributeToSelect(
                'is_anchor'
        )->addAttributeToFilter(
                'is_active',
                1
        )->addAttributeToFilter('include_in_menu', 1)->addIdFilter(
                $category->getChildren()
        )->setOrder(
                'position',
                \Magento\Framework\DB\Select::SQL_ASC
        )->joinUrlRewrite()->load();

        $categories = [];
        foreach ($collection as $subCat) {
            if ($subCat->getId() == 299)
                continue;

            if ($subCat->getName() == 'Shop by Age')
                continue;
                
            if ($subCat->getName() == 'Shop by gender')
                continue;

            $child = $objectManager->create('Magento\Catalog\Model\Category')->load($subCat->getId());
            $media_url = $objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                
            $categories[$subCat->getId()] = [
                'id' => $subCat->getId(),
                'name' => $subCat->getName(),
                'parent_id' => $subCat->getParentId(),
                'parent_name' => $subCat->getParentCategory()->getName(),
                'is_active' => $subCat->getIsActive(),
                'position' => (!empty($subCat->getPostion()) ? $subCat->getPostion() : 0),
                'level' => $subCat->getLevel(),
                'product_count' => $child->getProductCollection()->count(),
                'image' => (!empty($child->getImageUrl()) ? $child->getImageUrl() : ''),
                //'banner_image_url' => $this->getCategoryThumbUrl($child),
                'banner_image_url' => (!empty($child->getAppBanner()) ? $media_url.'catalog/category/'.$child->getAppBanner() : ''),
                'children_data' => !empty($child->getData('children_count')) ? $this->getChild($child) : []
            ];
        }
        ksort($categories);
        $categories = array_values($categories);
        return $categories;
    }

    public function getCategories($category) {
        // $tree = $this->_objectManager->create('\Yalla\Apis\Api\Data\CategoryTreeInterface');
        $tree = [];

        $image = (!empty($category->getImageUrl()) ? $category->getImageUrl() : '');
        $child = !empty($category->getData('children_count')) ? $this->getChild($category) : [];

        $scopeConfig = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $squareImage = $scopeConfig->getValue(
		    'imageupload/general/squareimage', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		if($squareImage){
			$squareImage = $this->storeManager->getStore()->getBaseUrl(
                                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                        ) . \Yalla\Theme\Model\Config\Backend\Image::UPLOAD_DIR . '/' . $squareImage;
		}
		
        /*$tree->setId($category->getId())
                ->setParentId($category->getParentId())
                ->setName($category->getName())
                ->setPosition($category->getPosition())
                ->setLevel($category->getLevel())
                ->setIsActive($category->getIsActive())
                ->setProductCount($category->getProductCount())
                ->setImage($image)
                ->setImageUrl('re')
                ->setChildrenData($child);*/
                
        $tree = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'parent_id' => $category->getParentId(),
                'is_active' => $category->getIsActive(),
                'position' => (!empty($category->getPostion()) ? $category->getPostion() : 0),
                'level' => $category->getLevel(),
                'product_count' => $category->getProductCollection()->count(),
                'banner_image_url' => $squareImage,
                'parent_name' => $category->getParentCategory()->getName(),
                'children_data' => $child
            ];

		$object = new \stdClass();
		foreach ($tree as $key => $value)
		{
    		$object->$key = $value;
		}
		
        return $tree;
    }

    public function getCategoryThumbUrl($category) {
        $url = '';
        $image = $category->getCategoryBanner();

        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        if ($image) {
            if (is_string($image)) {
                $url = $storeManager->getStore()->getBaseUrl(
                                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                        ) . 'catalog/category/' . $image;
            }
        }

        return $url;
    }

    public function getChildCategories($category_id) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $category = $objectManager->create('Magento\Catalog\Model\Category')->load($category_id);
        $collection = $category->getCollection();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection->addAttributeToSelect(
                'url_key'
        )->addAttributeToSelect(
                'name'
        )->addAttributeToSelect(
                'all_children'
        )->addAttributeToSelect(
                'is_anchor'
        )->addAttributeToFilter(
                'is_active',
                1
        )->addAttributeToFilter('include_in_menu', 1)->addIdFilter(
                $category->getChildren()
        )->setOrder(
                'position',
                \Magento\Framework\DB\Select::SQL_ASC
        );

        $collection->getSelect();

        $media_url = $objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

		$scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $offer_category_id = $scopeConfig->getValue(
		    'offercategory/general/category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		
        $categories = [];
        foreach ($collection as $subCat) {
        	$subcat_id = $subCat->getId();
            if($subCat->getId() == 431){
            	$subcat_id = $offer_category_id;
            }
            
            if ($subCat->getName() == 'Pre Order')
            	continue;
            
            //if ($subCat->getName() == 'Party')
            	//continue;
            	
            $child = $objectManager->create('Magento\Catalog\Model\Category')->load($subcat_id);
            if($subCat->getId() != 33){
		        $categories[$child->getId()] = [
		            'id' => (string) $child->getId(),
		            'name' => $child->getName(),
		            'parent_id' => $child->getParentId(),
		            'is_active' => $child->getIsActive(),
		            'position' => (!empty($child->getPostion()) ? $child->getPostion() : 0),
		            'level' => $child->getLevel(),
		            'product_count' => $child->getProductCollection()->count(),
		            'image' => (!empty($child->getAppBanner()) ? $media_url.'catalog/category/'.$child->getAppBanner() : ''),
		            'has_children' => !empty($child->getData('children_count')) ? 1 : 0
		        ];
            }
        }
        
        $categories = array_values($categories);
        return $categories;
    }

	/**
     * {@inheritdoc}
     */
    public function getTreeCeligo($rootCategoryId = null, $depth = null) {

        $category = null;
        if ($rootCategoryId !== null) {
            /** @var \Yalla\Apis\Model\Category $category */
            $category = $this->categoryRepository->get($rootCategoryId);
        } elseif ($this->isAdminStore()) {
            $category = $this->getTopLevelCategory();
        }
        //$result = $this->categoryTree->getTree($this->categoryTree->getRootNode($category), $depth);
        $result = $this->getAllCategories($category);
        return $result;
    }
    
    public function getAllCategories($category){
		$tree = $this->_objectManager->create('\Yalla\Apis\Api\Data\CategoryTreeInterface');

		if(!$category) return;
		
        $image = (!empty($category->getImageUrl()) ? $category->getImageUrl() : '');
        $child = !empty($category->getData('children_count')) ? $this->getChildCelgio($category) : [];
		
        $tree->setId($category->getId())
                ->setParentId($category->getParentId())
                ->setName($category->getName())
                ->setPosition($category->getPosition())
                ->setLevel($category->getLevel())
                ->setIsActive($category->getIsActive())
                ->setProductCount($category->getProductCount())
                ->setImage($image)
                ->setChildrenData($child);
		
        return $tree;
	}
	
	public function getChildCelgio($category) {
        $objectManager = $this->getObjectManager();
        $collection = $category->getCollection();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection->addAttributeToSelect(
                'url_key'
        )->addAttributeToSelect(
                'name'
        )->addAttributeToSelect(
                'all_children'
        )->addAttributeToSelect(
                'is_anchor'
        )->addIdFilter(
                $category->getChildren()
        )->setOrder(
                'position',
                \Magento\Framework\DB\Select::SQL_ASC
        )->joinUrlRewrite()->load();

        $categories = [];
        foreach ($collection as $subCat) {

            $child = $objectManager->create('Magento\Catalog\Model\Category')->load($subCat->getId());
            $media_url = $objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                
            $categories[$subCat->getId()] = [
                'id' => $subCat->getId(),
                'name' => $subCat->getName(),
                'parent_id' => $subCat->getParentId(),
                'is_active' => $subCat->getIsActive(),
                'position' => (!empty($subCat->getPostion()) ? $subCat->getPostion() : 0),
                'level' => $subCat->getLevel(),
                'product_count' => $child->getProductCollection()->count(),
                'image' => (!empty($child->getImageUrl()) ? $child->getImageUrl() : ''),
                //'banner_image_url' => $this->getCategoryThumbUrl($child),
                'banner_image_url' => (!empty($child->getAppBanner()) ? $media_url.'catalog/category/'.$child->getAppBanner() : ''),
                'children_data' => !empty($child->getData('children_count')) ? $this->getChild($child) : []
            ];
        }
        ksort($categories);
        $categories = array_values($categories);
        return $categories;
    }
}
