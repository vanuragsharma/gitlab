<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

class AddXOrders extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{
    public function execute()
    {
        $cartbinsize = $this->getRequest()->getParam('cartbinsize');
        $userId = $this->_preparationRegistry->getCurrentOperatorId();
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();

        try
        {
            $count = $this->_orderPreparationFactory->create()->populateBinCart($warehouseId, $userId);
            $this->messageManager->addSuccess(__('%1 orders have been added', $count));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
        }

        $this->_redirect('*/*/index');
    }
}
