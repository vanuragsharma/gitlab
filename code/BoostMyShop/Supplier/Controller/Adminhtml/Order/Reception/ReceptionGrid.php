<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order\Reception;

class ReceptionGrid extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
