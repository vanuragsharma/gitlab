<?php

namespace Yalla\Apis\Model;

use Yalla\Apis\Api\CategoryManagementInterface;
use Yalla\Apis\Api\ClientsInterface;

class Clients implements ClientsInterface {

    protected $dataHelper;
    protected $request;
    protected $_objectManager;

	public function __construct(
            \Yalla\Apis\Api\CategoryManagementInterface $categoryManagement,
            \Magento\Framework\App\Request\Http $request,
            \Yalla\Clients\Helper\Data $helper
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
		$model = $this->_objectManager->create('\Yalla\Clients\Model\Clients');
		
		$clients =$model->getCollection() ->addFieldToFilter('status',1);
		$clients = $clients->getData();
		foreach($clients as $client){
			$client['client_logo'] = $this->_helper->getBannerUrl($client['client_logo']);
			$list[] = $client;
		}
		echo json_encode(['status' => 'true', 'msg' => 'success', 'collection' => $list]);
		exit;
	}    
}
