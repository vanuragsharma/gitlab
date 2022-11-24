<?php

namespace Yalla\Apis\Model;

use Magento\Framework\App\ObjectManager;

class ShippingMethodManagement {
 
    public function afterEstimateByExtendedAddress($shippingMethodManagement, $output)
    {
        return $this->filterOutput($output);
    }
    private function filterOutput($output)
    {
        $shipping_methods = [];
        $free_shipping_available = false;
        foreach ($output as $shippingMethod) {
            if ($shippingMethod->getCarrierCode() == 'freeshipping' && $shippingMethod->getMethodCode() == 'freeshipping') {
                $free_shipping_available = true;
            }
        }
        foreach ($output as $shippingMethod) {
            if ($shippingMethod->getCarrierCode() == 'flatrate' && $shippingMethod->getMethodCode() == 'flatrate' && $free_shipping_available) {
                continue;
            }
            $shipping_methods[] = $shippingMethod;
        }
        if (count($shipping_methods)) {
            return $shipping_methods;
        }
        return $output;
    }
}
