<?php

namespace BoostMyShop\Supplier\Block\ProductSupplier\Renderer;

use Magento\Framework\DataObject;

class AvailabilityDate extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        if ($row->getsp_id()) {
            $name = 'products['.$row->getsup_id().']['.$row->getentity_id().'][sp_availability_date]';
            return '<input type="text" size="10" name="'.$name.'" id="'.$name.'" value="'.$row->getsp_availability_date().'"> ';
        }
    }

    public function renderExport(DataObject $row)
    {
        return $row->getsp_availability_date();
    }

}