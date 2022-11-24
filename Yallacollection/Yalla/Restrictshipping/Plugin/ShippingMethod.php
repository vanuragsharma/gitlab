<?php

namespace Yalla\Restrictshipping\Plugin;

class ShippingMethod
{

   protected $storeManager;

   public function __construct(
    \Magento\Store\Model\StoreManagerInterface $storeManager
   )
    {
        $this->storeManager = $storeManager;
    }
 
    public function aroundCollectCarrierRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Closure $proceed,
        $carrierCode,
        $request
    )
    {
        // To disable the shipping method   
        //if (($carrierCode == 'freeshipping' || $carrierCode == 'flatrate') && ($this->getStoreCode() == 'bahrain_english' || $this->getStoreCode() == 'bahrain_arabic')) {
            //return false;
        //} 
           // To enable the shipping method
            return $proceed($carrierCode, $request);
    }

    public function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }
	
}
