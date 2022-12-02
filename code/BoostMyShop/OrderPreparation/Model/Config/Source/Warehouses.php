<?php

namespace BoostMyShop\OrderPreparation\Model\Config\Source;

//rewitten by advanced stock module, returns only whs with shipping fulfilment type
class Warehouses implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [1 => 'Default'];
    }

}
