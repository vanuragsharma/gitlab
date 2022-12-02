<?php

namespace BoostMyShop\AdvancedStock\Block\StockMovement\Renderer;

use Magento\Framework\DataObject;

class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        $url = $this->getUrl('advancedstock/stockmovement/delete', ['sm_id' => $row->getsm_id()]);
        $action = "if (confirm('Are you sure ?')) { document.location.href = '".$url."'; }";
        return '<a href="#" onclick="'.$action.'">'.__('Delete').'</a>';
    }
}