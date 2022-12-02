<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class MassChangeShippingMethodForInProgress extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{

    protected $resultForwardFactory;

    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $inProgressIds = $this->getRequest()->getPost('massaction');
        if (!is_array($inProgressIds))
            $inProgressIds = explode(',', $inProgressIds);

        $newShippingMethod = $this->getRequest()->getPost('shipping_method');

        $errorCount = 0;
        $successCount = 0;

        foreach($inProgressIds as $inProgressId)
        {
            try
            {
                $inProgress = $this->_inProgressFactory->create()->load($inProgressId);
                $order = $this->_orderFactory->create()->load($inProgress->getip_order_id());
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
            $this->messageManager->addError(__('%1 shipping methods have not been changed', $errorCount));

        $this->_redirect('*/*/index');

    }
}
