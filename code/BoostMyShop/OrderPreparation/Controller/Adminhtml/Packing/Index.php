<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class Index extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Packing'));
        $this->_view->renderLayout();
        $storeId = $this->_coreRegistry->registry('current_packing_order')->getip_store_id();
        if($this->_coreRegistry->registry('current_packing_order')->getip_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED)
        {
            $id = $this->getRequest()->getParam('order_id');
            if($this->getRemoveOrderWhenShipped($storeId))
                $this->_orderPreparationFactory->create()->remove($id);
            elseif ($this->isBatchEnable()) {
                $batchId = $this->_coreRegistry->registry('current_packing_order')->getip_batch_id();
                $batch = $this->_batchFactory->create()->load($batchId);
                $batch->updatedProgress("shipment");
                if((int)$batch->getbob_progress() == 100)
                    $batch->markAsComplete();
                else
                    $batch->updateStatus(\BoostMyShop\OrderPreparation\Model\Batch::STATUS_INPROGRESS);
            }
        };
    }

    public function getRemoveOrderWhenShipped($storeId)
    {
        return $this->_configFactory->create()->getRemoveOrderWhenShipped($storeId) ? true : false;
    }

    public function isBatchEnable()
    {
        return $this->_configFactory->create()->isBatchEnable() ? true : false;
    }
}
