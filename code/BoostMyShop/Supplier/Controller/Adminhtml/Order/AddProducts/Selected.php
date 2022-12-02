<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order\AddProducts;

class Selected extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
