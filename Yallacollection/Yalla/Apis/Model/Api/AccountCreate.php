<?php

namespace Yalla\Apis\Model\Api;

use Yalla\Apis\Helper\ApiData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AccountCreate
 * @package Yalla\Apis\Model\Api
 */
class AccountCreate
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
     * AccountCreate constructor.
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
		//var_dump($postData);die();
        
        $guest = 0;
        if(isset($postData['guest']) && $postData['guest']){
        	$postData['firstname'] = 'Guest';
        	$postData['lastname'] = 'Guest';
        	$postData['password'] = "account_".rand(100000, 999999);
        	$guest = 1;
        }
        if ($error = $this->validate($postData, $guest)) {
            return array(
                [
                    'success' => 'false',
                    'msg' => $error,
                    'collection' => []
                ]
            );
        }
        $data = [
            "prefix" => isset($postData['prefix']) ? $postData['prefix'] : '',
            "mobile" => $postData['mobile'],
            "password" => $postData['password'],
            "firstname" => $postData['firstname'],
            "lastname" => $postData['lastname'],
            "email" => $postData['email'],
            "newsletter_subscription" => isset($postData['newsletter_subscription']) ? $postData['newsletter_subscription'] : 0,
        ];

        $response = $this->helper->createPost($data);
        
        $returnArr = array();
        $customerData = array();
        if($response['success'] != 'false'){
            $msg = $response['successmsg'];
            $customerData = $response['customer'];
        }else{
            $msg = $response['errormsg'];
        }
        
        $customerData['guest'] = $guest;
        
        $returnArr = [
            'success' => $response['success'],
            'msg' => $msg,
            'collection' => $customerData
        ];
        
        echo json_encode($returnArr);
	exit;
    }
    
    private function validate($data, $guest) {
        $error = '';
        if ((!isset($data['mobile']) || empty($data['mobile'])) && !$guest) {
            $error = 'Mobile number is a mandatory.';
        } else if (!isset($data['email']) || empty($data['email'])) {
            $error = 'Email is a mandatory field!';
        } else if (!isset($data['password']) || empty($data['password'])) {
            $error = 'Password is a mandatory field!';
        } else if (!isset($data['firstname']) || empty($data['firstname'])) {
            $error = 'Firstname is a mandatory field!';
        } else if (!isset($data['lastname']) || empty($data['lastname'])) {
            $error = 'Lastname is a mandatory field!';
        }

        return $error;
    }
}
