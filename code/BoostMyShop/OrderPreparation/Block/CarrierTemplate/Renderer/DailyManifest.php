<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class DailyManifest extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        $url = $this->getUrl('*/*/manifest', ['id' => $row->getId()]);
        return '<input type="button" value="'.__('Print').'" onclick="document.location.href = \''.$url.'\';">';
    }
}