<?php

namespace BoostMyShop\OrderPreparation\Model\Config\Source;

//rewitten by advanced stock module, returns all whs
class WarehousesAll implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [1 => 'Default'];
    }

}
