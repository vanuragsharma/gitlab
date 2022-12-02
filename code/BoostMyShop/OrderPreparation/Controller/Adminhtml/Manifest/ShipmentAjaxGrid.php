<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest;

use Magento\Framework\Controller\ResultFactory;

class ShipmentAjaxGrid extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest
{
    public function execute()
    {
        $manifestId = $this->getRequest()->getParam("bom_id");
        $manifest = $this->_manifestFactory->create()->load($manifestId);
        $this->_registry->register('current_manifest', $manifest);
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
