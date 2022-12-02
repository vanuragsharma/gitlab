<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class ChangeShippingMethod extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $id = $this->getRequest()->getParam('id');

        try
        {
            $method = $this->getRequest()->getParam('method');
            $inProgress = $this->_inProgressFactory->create()->load($id);
            $order = $this->_orderFactory->create()->load($inProgress->getip_order_id());
            $this->_carrierHelper->changeShippingMethod($order, $method);

            $this->messageManager->addSuccess(__('Shipping method updated'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
        }

        $this->_redirect('*/*/Index', ['order_id' => $id]);

    }
}
