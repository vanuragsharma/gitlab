<?php

namespace Yalla\Apis\Plugin;

class Shipping
{
	protected $product;
 
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $product
    ) {
        $this->product = $product; 
    }
 
    public function aroundCollectCarrierRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Closure $proceed,
        $carrierCode,
        $request
    ) {
    	$postData = file_get_contents("php://input");
        if (!empty($postData)) {
            $params = json_decode($postData, true);
        }
        
        $expressShipping = true;
        $allItems = $request->getAllItems();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $itemHelper = $objectManager->create('Mageplaza\PreOrder\Helper\Item');
        
        $quote_has_preorder = false;
        // iterate all cart products to check if no_free_shipping is true
        foreach ($allItems as $item) {    
            $_product = $this->product->create()->load($item->getProduct()->getId());
            $isPreOrder = $itemHelper->isApplyForProduct($item->getProduct());
            if ($isPreOrder) {
                $quote_has_preorder = true;
            }
            
            // if product has no_free_shipping true
            
            if ($_product->getShipping() != 5487) {
                $expressShipping = false;
                break;
            }
        }
        
        if(isset($params['customer_id'])){
 			$quote = $objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($params['customer_id']);
 		}else{
 			$cart = $objectManager->get('\Magento\Checkout\Model\Cart');
 			$quote = $cart->getQuote();
 		}
        
        $_date = $objectManager->get('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
		$currentTime = $_date->date()->format('Y-m-d H:i:s');
		if (strtotime($currentTime) > strtotime('16:00')){
			$expressShipping = false;
		}
		
        // if no_free_shipping is yes and shipping method free shipping return nothing
        if (!$expressShipping && $carrierCode == 'flatrateone') {
            return false;
        }
        
        if($carrierCode == 'flatrate'){
	 		if($quote){
				if($quote->getSubtotal() >= 100 && $carrierCode == 'flatrate'){
					return false;
				}
		    }
        }
        
        if($quote_has_preorder){
        	if ($carrierCode != 'flatrate' && $carrierCode != 'freeshipping' ) {
		        return false;
		    }
        }
 
        $result = $proceed($carrierCode, $request);
        return $result;
    }
}
