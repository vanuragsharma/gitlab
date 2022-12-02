<?php

namespace BoostMyShop\OrderPreparation\Model\Config\Source;

class SortMode implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [    'location_name' => __('By location then name'),
                    'name' => __('By name'),
                    'sku' => __('By sku')
                ];
    }

}
