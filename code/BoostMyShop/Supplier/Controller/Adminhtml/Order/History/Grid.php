<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order\History;

/**
 * Class Grid
 * @package BoostMyShop\Supplier\Controller\Adminhtml\Order\History
 */
class Grid extends \BoostMyShop\Supplier\Controller\Adminhtml\Order {

    public function execute()
    {
        $poId = $this->getRequest()->getParam('po_id');
        $model = $this->_orderFactory->create();
        $model->load($poId);

        $this->_coreRegistry->register('current_purchase_order', $model);

        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

}