<?php

namespace BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class OrdersToShip extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'ErpProduct/Edit/Tab/OrdersToShip.phtml';

    public function getTabLabel()
    {
        return __('Orders to ship');
    }

    public function getTabTitle()
    {
        return __('Orders to ship');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

}