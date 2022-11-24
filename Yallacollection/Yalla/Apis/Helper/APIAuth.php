<?php 
namespace Yalla\Apis\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class APIAuth extends AbstractHelper {

  const ACCESSKEY = 'jgp1ayw80zictyr199q6eli0v53lfezs';

   public function __construct(
		\Magento\Sales\Model\OrderFactory $orderModel
		   ) {

		$this->orderModel = $orderModel;

   }
   
   public function Auth() {

		try{
			$tokens = null;
			$headers = $this->apache_request_headers();
			  if(isset($headers['AUTHORIZATION'])){
				$matches = explode(" ", $headers['AUTHORIZATION']);
				if(isset($matches[1])){
				  $tokens = $matches[1];
				}
			  }
			if(strcmp($tokens,self::ACCESSKEY) != 0){
				$data = ['success' => "false", 'msg' => 'There was an error processing the request. Unauthorized Request.'];
			}else{
				$data = ['success' => "true", 'msg' => 'Success'];
			}
			return $data;
		}
			catch (\Exception $e) {
				$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/apiauth.log');
				$this->log = new \Zend\Log\Logger();
				$this->log->addWriter($writer);
				       $this->log->info($e);
				       $this->log->info("======================= API Unauthorized ========================");die;
		}

				       
		   }

		Public function apache_request_headers() {
		 $arh = array();
		 $rx_http = '/\AHTTP_/';
		 foreach($_SERVER as $key => $val) {
		   if( preg_match($rx_http, $key) ) {
			 $arh_key = preg_replace($rx_http, '', $key);
			 $rx_matches = array();
			 // do some nasty string manipulations to restore the original letter case
			 // this should work in most cases
			 $rx_matches = explode('_', $arh_key);
			 if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
			   foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
			   $arh_key = implode('-', $rx_matches);
			 }
			 $arh[$arh_key] = $val;
		   }
		 }
		 return( $arh );
		}


}





