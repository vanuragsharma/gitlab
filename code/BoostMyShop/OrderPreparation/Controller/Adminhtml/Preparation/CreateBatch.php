<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class CreateBatch extends \Magento\Backend\App\AbstractAction
{
    protected $_batchHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \BoostMyShop\OrderPreparation\Model\BatchHelper $batchHelper
    )
    {
        parent::__construct($context);
        $this->_batchHelper = $batchHelper;
    }

    public function execute()
    {
        try {
            $warehouseId = $this->getRequest()->getParam("wh_id");
            $type = $this->getRequest()->getParam("type");

            $carrier = null;
            if($this->getRequest()->getParam("carrier"))
                $carrier = $this->getRequest()->getParam("carrier");

            $orderIds = [];
            if($this->getRequest()->getParam("p_id"))
                $orderIds = $this->_batchHelper->getTypeInstance($type)->getCandidateOrdersByProductId($warehouseId, $this->getRequest()->getParam("p_id"), $carrier);

            $batch = $this->_batchHelper->createNewBatch($warehouseId, $type, $carrier, $orderIds);

            $this->messageManager->addSuccess(__('Batch %1 / %2 successfully created', $batch->getbob_label(), $type));
            $this->_redirect('*/*/index');

        }catch(\Exception $e)
        {
            $this->messageManager->addError(__('%1', $e->getMessage()));
            $this->_redirect('*/*/index');
        }
    }
}
