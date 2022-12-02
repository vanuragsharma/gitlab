<?php

namespace BoostMyShop\Margin\Block\Widget\Grid\Column\Renderer\Cogs;

use Magento\Framework\DataObject;

class Order extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(DataObject $row)
    {
        return $this->cleanReference($row->getincrement_id());
    }

    public function cleanReference($reference)
    {
        $t = explode('_', $reference);
        if (count($t) > 1)
            unset($t[0]);
        return implode('_', $t);
    }


}