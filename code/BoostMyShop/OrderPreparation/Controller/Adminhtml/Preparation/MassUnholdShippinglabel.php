<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Sales\Model\Order;

class MassUnholdShippinglabel extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{

    public function execute()
    {
        $orderIds = $this->getRequest()->getPost('massaction');
        $errorCount = 0;
        $successCount = 0;

        foreach($orderIds as $orderId)
        {
            try
            {
                $order = $this->_orderFactory->create()->load($orderId);
                $order->setState(Order::STATE_PROCESSING)->setStatus(Order::STATE_PROCESSING);
                $order->save();
                $successCount++;
            }
            catch(\Exception $ex)
            {
                $errorCount++;
            }
        }

        $this->messageManager->addSuccess(__('Status unholded for %1 orders.', $successCount));
        if ($errorCount > 0)
            $this->messageManager->addError(__('%1 status have not been unholded.', $errorCount));

        $this->_redirect('*/*/index');

    }
}
