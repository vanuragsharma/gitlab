<?php

namespace Yalla\Apis\Model;

use Yalla\Apis\Api\CategoryManagementInterface;
use Yalla\Apis\Api\GalleryInterface;

class Gallery implements GalleryInterface {

    protected $dataHelper;
    protected $request;
    protected $_objectManager;

	public function __construct(
            \Yalla\Apis\Api\CategoryManagementInterface $categoryManagement,
            \Magento\Framework\App\Request\Http $request,
            \Yalla\Apis\Helper\Data $helper
    ) {
        $this->categoryManagement = $categoryManagement;
        $this->request = $request;
        $this->_helper = $helper;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }
    
	public function getList(){

		$api_auth = $this->_objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
		$storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
		$media_url = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$resource = $this->_objectManager->create('\Magento\Framework\App\ResourceConnection');
		
		$collection = $this->_objectManager->create('\Lof\Gallery\Model\Category')
        ->getCollection()
        ->addFieldToFilter("is_active", 1)
        ->addStoreFilter($storeManager->getStore())
        ->setOrder("cat_position", "ASC");

        //$collection->getSelect()
        //->limit($itemPerPage);

		$banner_collection = $this->_objectManager->create('\Lof\Gallery\Model\Banner')
        ->getCollection()
        ->addFieldToFilter("main_table.is_active", 1);

        $banner_collection->getSelect()
        ->joinLeft(
            [
            'lg' => $resource->getTableName('lof_gallery')],
            'lg.banner_id = main_table.banner_id',
            [
            'id' => 'category_id',
            'position' => 'position'
            ]
            )
        ->joinLeft(
            [
            'lgbc' => $resource->getTableName('lof_gallery_banner_category')],
            'lgbc.category_id = lg.category_id',
            [
            'category_id' => 'category_id'         
            ]    
            )
        ->group('main_table.banner_id');
        
        $banners = $banner_collection->getData();
        
        $categories = [];
        foreach ($collection as $_cat) {
        	$gallery = [];
        	foreach($banners as $banner){
        		if($banner['category_id'] == $_cat->getData('category_id')){
		    		$gallery[] = [
		    			'image_id' => $banner['banner_id'],
		    			'image' => $media_url.$banner['file']
		    		];
        		}
        	}
            $categories[] = [
            	'gallery_id' => $_cat->getData('category_id'),
            	'gallery_name' => $_cat->getData('name'),
            	'gallery_images' => $gallery
            ];
        }
		echo json_encode(['status' => 'true', 'msg' => 'success', 'collection' => $categories]);
		exit;
	}    
}
