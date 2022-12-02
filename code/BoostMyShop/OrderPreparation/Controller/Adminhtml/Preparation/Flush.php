<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Flush extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{
    public function execute()
    {
        $this->_orderPreparationFactory->create()->flush();

        if($this->_configFactory->create()->getFlushPackedOrders())
            $this->messageManager->addSuccess(__('Packed and shipped orders removed from in progress tab'));
        else
            $this->messageManager->addSuccess(__('Shipped orders removed from in progress tab'));

        $this->_redirect('*/*/index');
    }
}
