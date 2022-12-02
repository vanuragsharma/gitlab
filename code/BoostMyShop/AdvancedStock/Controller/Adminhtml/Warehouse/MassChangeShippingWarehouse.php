<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse;

class MassChangeShippingWarehouse extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse
{

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        try
        {
            $warehouseId = $this->getRequest()->getParam('w_id');
            $targetWarehouseId = $data['target_warehouse'];
            $itemIds = $data['massaction'];
            if (!is_array($itemIds))
                $itemIds = expode(',', $itemIds);

            foreach($itemIds as $itemId)
            {
                $this->changeWarehouse($itemId, $targetWarehouseId);
            }

            $this->messageManager->addSuccess(__('%1 order items changed', count($itemIds)));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
        }


        $this->_redirect('*/*/Edit', ['w_id' => $warehouseId]);
    }

    protected function changeWarehouse($orderItemId, $targetWarehouseId)
    {
        $extendedItem = $this->_extendedSalesFlatOrderItemFactory->create()->loadByItemId($orderItemId);
        $extendedItem->setesfoi_warehouse_id($targetWarehouseId)->save();
    }

}
