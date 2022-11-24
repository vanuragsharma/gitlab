<?php

namespace Yalla\Apis\Model\Api;

use Yalla\Apis\Helper\ApiData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PostManagement
 * @package Yalla\Apis\Model\Api
 */
class PostManagement {

    /**
     * @var ApiData
     */
    protected $helper;

    /**
     * @var
     */
    protected $storeManager;

    /**
     * PostManagement constructor.
     * @param ApiData $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
            ApiData $helper,
            StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function getPost() {
        $postData = file_get_contents("php://input");
        $postData = json_decode($postData, true);
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
		// var_dump($postData['oldmobile']);
        if ($error = $this->validate($postData)) {
            return array(
                [
                    'success' => 'false',
                    'msg' => $error,
                    'collection' => []
                ]
            );
        }

        $data = ["mobilenumber" => $postData['mobilenumber'],
            "otptype" => $postData['otptype'],
            "email" => isset($postData['email']) ? $postData['email'] : '',
            "resendotp" => isset($postData['resendotp']) ? $postData['resendotp'] : 0,
            "oldmobile" => isset($postData['oldmobile']) ? $postData['oldmobile'] : ''
        ];
        
        $response = $this->helper->otpSave($data);
        
        $returnArr = array();

        if ($response['success'] != 'false') {
            $msg = $response['successmsg'];
        } else {
            $msg = $response['errormsg'];
        }

        $returnArr = [
            'success' => $response['success'],
            'msg' => $msg,
            'otp' => isset($response['otp']) ? $response['otp'] : '',
            'collection' => []
        ];

        echo json_encode($returnArr);
	exit;
    }

    private function validate($data) {
        $error = '';
        if (!isset($data['mobilenumber']) || empty($data['mobilenumber'])) {
            $error = 'Mobile number is a mandatory.';
        } else if (!isset($data['otptype']) || empty($data['otptype'])) {
            $error = 'Missing mandatory field!';
        } else if ($data['otptype'] == 'register' && (!isset($data['email']) || empty($data['email']))) {
            $error = 'Email is a mandatory field!';
        }

        return $error;
    }

}
