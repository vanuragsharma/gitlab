<?php

namespace Yalla\Apis\Model;

use Yalla\Apis\Api\ContactInterface;

class Contact implements ContactInterface {

	protected $_request;
	protected $_storeManager;
	protected $_objectManager;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\App\Request\Http $request, array $data = []) {
        $this->_storeManager = $storeManager;
		$this->_request = $request;
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * 
     * @return array
    */
    public function contact() {
		
		
		$api_auth = $this->_objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        $postData = file_get_contents("php://input");
        if (!empty($postData)) {
            $request = json_decode($postData, true);
        }
		
		$subject = "Contact Form Submitted";
        $to = "contactus@yallatoys.com";
        $from = strip_tags($request['email']);

		$message = '<html><body>';
        $message .= '<div style="max-width: 100%;display:block;">';
        $message .= '<div style="max-width: 100%;display:block;font-size: 18px;">New Contact Request</div><br/><br/>';
        $message .= '<table style="width: 60%;font-family:\'Tahoma\'; border: 1px solid black; border-collapse: collapse">';

        $message .= '<tr>
                        <th style="width: 20%;padding: 10px;">Name</th>
                        <td style="width: 20%;padding: 10px;">'.$request['name'].'</td>
                    </tr>';

        $message .= '<tr>
                        <th style="width: 20%;padding: 10px;">email</th>
                        <td style="width: 20%;padding: 10px;">'.$request['email'].'</td>
                    </tr>';

        $message .= '<tr>
                        <th style="width: 20%;padding: 10px;">Phone</th>
                        <td style="width: 20%;padding: 10px;">'.$request['phone'].'</td>
                    </tr>';

        $message .= '<tr>
                        <th style="width: 20%;padding: 10px;">Comments</th>
                        <td style="width: 20%;padding: 10px;">'.$request['comments'].'</td>
                    </tr>';

        $message .= '</table>';

        $message .= '</body></html>';

        $headers = "From: " . strip_tags($from) . "\r\n";
        $headers .= "Reply-To: " . $to . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        mail($to, $subject, $message, $headers);

        $response = ['success' => 'true', 'msg' => __('Your request has been received.')];
		echo json_encode($response);
		exit;
    }

}


