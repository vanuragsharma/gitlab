<?php

namespace BoostMyShop\Supplier\Model\Source\OrderProduct;

class LandingCostMethod
{


    public function toOptionArray()
    {
        $options = array();

        $options[] = array('value' => 'quantity', 'label' => 'Distribute considering product quantity');
        $options[] = array('value' => 'value', 'label' => 'Distribute considering product buying price');
        $options[] = array('value' => 'weight', 'label' => 'Distribute considering product weight');


        return $options;
    }
}
