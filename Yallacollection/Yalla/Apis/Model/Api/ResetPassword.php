<?php

namespace Yalla\Apis\Model\Api;

use Yalla\Apis\Helper\ApiData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ResetPassword
 * @package Yalla\Apis\Model\Api
 */
class ResetPassword
{

    /**
     * @var ApiData
     */
    protected $helper;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ResetPassword constructor.
     * @param ApiData $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ApiData $helper,
        StoreManagerInterface $storeManager
    )
    {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
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

        $response = [
            'success' => "true",
            'msg' => ""
        ];

	$mobilenumber = $postData['mobilenumber'];
	$password = $postData['password'];

        try {

            $customer = $this->helper->getCustomerCollectionMobile($mobilenumber);
            if ($customer->getId()) {
                $customer->setMobileNumber($mobilenumber);
                $customer->setRpToken($customer->getRpToken());
                $customer->setPassword($password);
                $customer->save();
                //$response['customurl'] = $this->storeManager->getStore()->getUrl('customer/account/login');
                $response['msg'] = 'Password has been changed successfully. You can now login with your new credentials.';
            } else {
                $response['msg'] = 'Password change error, please try again.';
                $response['success'] = "false";
            }

            $returnArr = json_encode($response);
            echo $returnArr;
	    exit;

        } catch (\Exception $e) {
            $response['msg'] = 'Password change error, please try again.';
            $response['success'] = "false";

            $returnArr = json_encode($response);
            echo $returnArr;
	    exit;
        }
    }

    private function validate($data) {
        $error = '';
        if (!isset($data['mobilenumber']) || empty($data['mobilenumber'])) {
            $error = 'Mobile number is a mandatory.';
        } else if (!isset($data['password']) || empty($data['password'])) {
            $error = 'Missing mandatory field!';
        }

        return $error;
    }

}
