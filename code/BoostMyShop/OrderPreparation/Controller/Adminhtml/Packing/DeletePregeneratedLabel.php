<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class DeletePregeneratedLabel extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    public function execute()
    {
        $this->_initAction();

        $orderInProgress = $this->currentOrderInProgress();

        try
        {
            $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

            $orderInProgress->CancelPregeneratedLabel();

            $this->messageManager->addSuccess(__('Pre-generated label deleted'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
        }
    }

    public function currentOrderInProgress()
    {
        return $this->_coreRegistry->registry('current_packing_order');
    }
}