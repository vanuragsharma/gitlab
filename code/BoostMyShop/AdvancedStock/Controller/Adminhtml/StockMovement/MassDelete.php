<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockMovement;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockMovement
{

    /**
     * @return void
     */
    public function execute()
    {
        try
        {
            $smIds = $this->getRequest()->getPostValue('massaction');
            foreach($smIds as $smId)
            {
                $stockMovement = $this->_stockMovementFactory->create()->load($smId);
                $stockMovement->delete();
            }


            $this->messageManager->addSuccess(__('Stock movements deleted'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addSuccess(__('An error occured : %1', $ex->getMessage()));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
