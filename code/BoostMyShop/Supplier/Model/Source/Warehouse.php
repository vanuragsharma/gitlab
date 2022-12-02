<?php

namespace BoostMyShop\Supplier\Model\Source;

class Warehouse implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $options = array();
        $options[] = array('value' => 1, 'label' => 'Default');
        return $options;
    }
}