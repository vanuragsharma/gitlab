<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

/**
 * Class Barcode
 * @package BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer
 */
class Barcode extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        $html = $row->getProduct()->getData($this->getColumn()->getIndex());
        return $html;
    }
}