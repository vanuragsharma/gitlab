<?php

namespace BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class AllOrders extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'ErpProduct/Edit/Tab/AllOrders.phtml';

    public function getTabLabel()
    {
        return __('All Orders');
    }

    public function getTabTitle()
    {
        return __('All Orders');
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