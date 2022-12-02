<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\ErpProduct\AllOrders;

class Grid extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\ErpProduct
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->renderLayout();

    }
}
