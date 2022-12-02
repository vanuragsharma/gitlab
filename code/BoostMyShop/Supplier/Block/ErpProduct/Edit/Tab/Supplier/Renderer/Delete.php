<?php

namespace BoostMyShop\Supplier\Block\ErpProduct\Edit\Tab\Supplier\Renderer;

use Magento\Framework\DataObject;

class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        if ($row->getsp_id()) {
            $name = 'delete[' . $row->getsp_id() . ']';
            return '<input type="checkbox" name="' . $name . '" id="' . $name . '" ><style>.col-sp_moq input, .col-sp_pack_qty input, .col-sp_stock input{width:2em;}</style>';
        }
    }
}