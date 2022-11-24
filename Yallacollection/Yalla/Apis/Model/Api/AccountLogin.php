<?php

namespace Yalla\Apis\Model\Api;

use Yalla\Apis\Helper\ApiData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AccountLogin
 * @package Yalla\Apis\Model\Api
 */
class AccountLogin
{

    /**
     * @var ApiData
     */
    protected $helper;
    /**
     * @var
     */
    protected $storeManager;

    /**
     * AccountLogin constructor.
     * @param ApiData $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ApiData $helper,
        StoreManagerInterface $storeManager
    )
    {
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */

    public function getPost()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        $postData = file_get_contents("php://input");
        $postData = json_decode($postData, true);
        if ($error = $this->validate($postData)) {
            return array(
                [
                    'success' => 'false',
                    'msg' => $error,
                    'collection' => []
                ]
            );
        }
        
        $data = [
            "emailmobile" => $postData['emailmobile'],
            "password" => $postData['password']
        ];

        $response = $this->helper->loginPost($data);
        
        $returnArr = array();
        $customerData = array();
        if($response['success'] != 'false'){
            $msg = $response['successmsg'];
            $customerData = $response['customer'];
        }else{
            $msg = $response['errormsg'];
        }
        
        $returnArr = [
            'success' => $response['success'],
            'msg' => $msg,
            'collection' => $customerData
        ];
        
        echo json_encode($returnArr);
	exit;
    }
    
    private function validate($data) {
        $error = '';
        if (!isset($data['emailmobile']) || empty($data['emailmobile'])) {
            $error = 'Email/Mobile number is a mandatory.';
        } else if (!isset($data['password']) || empty($data['password'])) {
            $error = 'Password is a mandatory field!';
        }

        return $error;
    }
}
