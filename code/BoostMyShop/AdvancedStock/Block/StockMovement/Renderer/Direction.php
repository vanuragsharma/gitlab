<?php

namespace BoostMyShop\AdvancedStock\Block\StockMovement\Renderer;

use Magento\Framework\DataObject;

class Direction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        $html = '';

        $image = '';
        if (!$row->getsm_from_warehouse_id())
            $image = 'increase.png';
        if (!$row->getsm_to_warehouse_id())
            $image = 'decrease.png';

        if ($image)
        {
            $html = '<img src="'.$this->getViewFileUrl('BoostMyShop_AdvancedStock::images/'.$image).'" style="height: 15px; width: 15px; max-width: 15px;" />';
            if ($row->getsm_new_qty() !== null)
                $html .= ' '.$row->getsm_new_qty();
        }

        return $html;
    }
}