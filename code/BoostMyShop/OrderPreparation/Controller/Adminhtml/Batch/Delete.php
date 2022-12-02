<?php
namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Batch;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Delete extends \Magento\Backend\App\AbstractAction
{
    protected $_config = null;
    protected $_batchFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        \BoostMyShop\OrderPreparation\Model\BatchFactory $batchFactory
    )
    {
        parent::__construct($context);
        $this->_config = $config;
        $this->_batchFactory = $batchFactory;
    }

    public function execute()
    {
        try {
            $batchId = $this->getRequest()->getParam("bob_id");
            if (!$batchId) {
                throw new \Exception(__("No batch id found"));
            }

            $batch = $this->_batchFactory->create()->load($batchId);

            if($batch->getbob_status() != \BoostMyShop\OrderPreparation\Model\Batch::STATUS_READY_FOR_LABEL_GENERATION
                && $batch->getbob_status() != \BoostMyShop\OrderPreparation\Model\Batch::STATUS_READY
                && $batch->getbob_status() != \BoostMyShop\OrderPreparation\Model\Batch::STATUS_PRINTED
            ){
                throw new \Exception(__("This batch can not be deleted"));
            }

            $batch->delete();
            $this->messageManager->addSuccess(__('Batch sucessfully deleted'));
            $this->_redirect('orderpreparation/preparation/index/');

        }catch(\Exception $e)
        {
            $this->messageManager->addError(__('%1', $e->getMessage()));
            $this->_redirect('orderpreparation/preparation/index');
        }
    }
}