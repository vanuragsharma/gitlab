<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Replenishment;

class CreateOrder extends \BoostMyShop\Supplier\Controller\Adminhtml\Replenishment
{
    public function execute()
    {
        try
        {
            $data = $this->getRequest()->getPostValue();
            $supplierId = $data['sup_id'];
            $warehouseId = $data['warehouse_id'];
            if (!isset($data['products']))
                throw new \Exception('No products selected');
            $productIds = $data['products'];

            $tmp = [];
            $productIds = explode(';', $productIds);
            foreach($productIds as $item)
            {
                if ($item)
                {
                    list($productId, $qty) = explode('=', $item);
                    $tmp[$productId] = $qty;
                }
            }
            $productIds = $tmp;

            //assign first whs
            $warehouses = $this->_warehouse->toOptionArray();
            if ($warehouseId <= 0 && isset($warehouses[0]['value']))
                $warehouseId = $warehouses[0]['value'];

            $order = $this->_orderFactory->create();
            $order->applyDefaultData($supplierId);
            $order->setpo_warehouse_id($warehouseId);
            $order->save();

            foreach($productIds as $productId => $qty)
            {
                if ($qty > 0){
                    $order->addProduct($productId, $qty);
                }
            }

            $this->messageManager->addSuccess(__('Order created.'));
            $this->_redirect('supplier/order/edit', ['po_id' => $order->getId()]);

        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('An error occured : '.$ex->getMessage()));
            $this->_redirect('supplier/replenishment/index');
        }
    }

}
