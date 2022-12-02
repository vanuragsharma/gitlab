<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate;

class CostMatrix
{
    protected $_orderFactory;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->_orderFactory = $orderFactory;
    }


    public function sortByWeight($costMatrix) {
        usort($costMatrix, function($a, $b){
            if ($a["weight_below"] == $b["weight_below"])
                return 0;
            return ($a["weight_below"] < $b["weight_below"]) ? -1 : 1;
        });

        return $costMatrix;
    }

    public function getCost($carrierTemplate, $InProgress)
    {
        $shippingCost = 0;
        $boxes = json_decode($InProgress->getip_boxes(), true);
        $shippingMethod = $InProgress->getOrder()->getShippingMethod();
        $tmp  = unserialize($carrierTemplate->getct_cost_matrix());
        
        if (!is_array($tmp))
            return;
        
        $costMatrix = $this->sortByWeight($tmp);
        if(!empty($costMatrix)) {
            foreach ($boxes as $box) {
                foreach ($costMatrix as $data) {
                    if($data['shipping_method'] == 'all' || $data['shipping_method'] == $shippingMethod) {
                        if($data['weight_below'] >= $box['total_weight']) {
                            $shippingCost += $data['cost'];
                            break;
                        }
                    }
                }
            }

            //following code doesnt make sense, if no multi box, weight is total weight
            //if($carrierTemplate->getRenderer()->supportMultiboxes() == '')
            //    $shippingCost = $shippingCost*(int)$InProgress->getip_parcel_count();
        }

        return $shippingCost;
    }

}
