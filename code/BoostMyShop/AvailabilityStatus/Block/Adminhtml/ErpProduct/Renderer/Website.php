<?php

namespace BoostMyShop\AvailabilityStatus\Block\Adminhtml\ErpProduct\Renderer;

use Magento\Framework\DataObject;

class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{


    public function render(DataObject $row)
    {
        return $row->getWebsite()->getName();
    }


}