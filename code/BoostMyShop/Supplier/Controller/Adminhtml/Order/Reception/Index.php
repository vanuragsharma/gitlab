<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order\Reception;

class Index extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Reception'));
        $this->_view->renderLayout();
    }
}
