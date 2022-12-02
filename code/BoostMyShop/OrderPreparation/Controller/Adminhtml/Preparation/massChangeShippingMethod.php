<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class MassChangeShippingMethod extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{

    protected $resultForwardFactory;

    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $orderIds = $this->getRequest()->getPost('massaction');
        if (!is_array($orderIds))
            $orderIds = explode(',', $orderIds);

        $newShippingMethod = $this->getRequest()->getPost('shipping_method');

        $errorCount = 0;
        $successCount = 0;

        foreach($orderIds as $orderId)
        {
            try
            {
                $order = $this->_orderFactory->create()->load($orderId);
                $this->_carrierHelper->changeShippingMethod($order, $newShippingMethod);
                $successCount++;
            }
            catch(\Exception $ex)
            {
                $errorCount++;
            }
        }

        $this->messageManager->addSuccess(__('Shipping method changed for %1 orders', $successCount));
        if ($errorCount > 0)
            $this->messageManager->addError(__('%1 shipping methods have not been changed.', $errorCount));

        $this->_redirect('*/*/index');

    }
}
