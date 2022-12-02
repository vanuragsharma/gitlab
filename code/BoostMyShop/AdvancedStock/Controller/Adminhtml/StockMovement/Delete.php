<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockMovement;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class Delete extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockMovement
{

    /**
     * @return void
     */
    public function execute()
    {
        try
        {
            $smId = $this->getRequest()->getParam('sm_id');
            $stockMovement = $this->_stockMovementFactory->create()->load($smId);
            $stockMovement->delete();

            $this->messageManager->addSuccess(__('Stock movement deleted'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('An error occured : %1', $ex->getMessage()));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}
