<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class ImportReception extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $poId = $this->getRequest()->getParam('po_id');
        $model = $this->_orderFactory->create();
        $model->load($poId);
        $this->_coreRegistry->register('current_purchase_order', $model);

        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Import PO reception data from CSV'));
        $this->_view->renderLayout();
    }
}
