<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Batch;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Popup extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Batch
{
    public function execute()
    {
        $this->_initAction();

        $resultLayout = $this->_resultLayoutFactory->create();
        $this->_view->renderLayout();
    }
}
