<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class Notify extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    public function execute()
    {
        $poId = (int)$this->getRequest()->getParam('po_id');
        try
        {
            $order = $this->_orderFactory->create()->load($poId);
            if($order->getSupplier()->getsup_delayed_notification()) {
                if($order->getpo_status() == \BoostMyShop\Supplier\Model\Order\Status::expected)
                    throw new \Exception(__('Grouped notification are configured for this supplier, supplier will notify on scheduled hours'));
                else
                    throw new \Exception(__('Grouped notification are configured for this supplier, switch purchase order status to expected to schedule the notification'));
            }
            $this->_notification->notifyToSupplier($order);
            $this->messageManager->addSuccess(__('Supplier notified.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__($ex->getMessage()));
        }

        $this->_redirect('*/*/Edit', ['po_id' => $order->getId()]);
    }

}
