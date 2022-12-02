<?php

namespace BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Tab\OrdersToShip;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\PendingOrders\Grid
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('orderstoship');
    }


    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        return $this;
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getGridUrl()
    {
        return $this->getUrl('advancedstock/erpproduct_orderstoship/grid', ['product_id' => $this->getProduct()->getId()]);
    }




}
