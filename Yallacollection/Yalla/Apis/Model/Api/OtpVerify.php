<?php

namespace Yalla\Apis\Model\Api;

use Yalla\Apis\Helper\ApiData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class OtpVerify
 * @package Yalla\Apis\Model\Api
 */
class OtpVerify
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
     * OtpVerify constructor.
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
		
        $postData = file_get_contents("php://input");
        $postData = json_decode($postData, true);
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
		
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
            "otpcode" => $postData['otpcode'],
            "oldmobile" => isset($postData['oldmobile']) ? $postData['oldmobile'] : ''
        ];

        $response = $this->helper->otpVerify($data);
        $returnArr = array();
        $customerData = array();
        if ($response['success'] != 'false') {
            $msg = $response['successmsg'];
            if($postData['otptype'] == 'login'){
                $customerData = $response['customer'];
            }
        } else {
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
        if (!isset($data['mobilenumber']) || empty($data['mobilenumber'])) {
            $error = 'Mobile number is a mandatory.';
        } else if (!isset($data['otpcode']) || empty($data['otpcode'])) {
            $error = 'Otp is a mandatory field!';
        } else if (!isset($data['otptype']) || empty($data['otptype'])) {
            $error = 'Missing mandatory field!';
        }

        return $error;
    }
}
