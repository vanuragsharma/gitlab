<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Batch;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PrintAction extends \Magento\Backend\App\AbstractAction
{
    protected $resultForwardFactory;
    protected $_batchFactory;
    protected $_batchHelper;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \BoostMyShop\OrderPreparation\Model\BatchFactory $batchFactory,
        \BoostMyShop\OrderPreparation\Model\BatchHelper $batchHelper
    ){
        parent::__construct($context);
        $this->_batchFactory = $batchFactory;
        $this->_batchHelper = $batchHelper;
    }

    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        try {
            $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

            $bob_id = $this->getRequest()->getParam('bob_id');
            if (!$bob_id)
                throw new \Exception(__('No batch ID provided'));

            $batch = $this->_batchFactory->create()->load($bob_id);
            if(!$batch)
                throw new \Exception(__('No batch found with ID %1', $bob_id));

            if($batch->getbob_status() == \BoostMyShop\OrderPreparation\Model\Batch::STATUS_READY_FOR_LABEL_GENERATION
                || $batch->getbob_status() == \BoostMyShop\OrderPreparation\Model\Batch::STATUS_NEW
            ) {
                throw new \Exception(__('No shipping label generated yet for batch #%1', $batch->getbob_label()));
            }

            $pdfClass = $this->_batchHelper->getTypeInstance($batch->getbob_type())->getPdfClass();
            $pdf = $this->_objectManager->create($pdfClass)->getPdf([$batch]);
            $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');

            if($batch->getbob_status() == \BoostMyShop\OrderPreparation\Model\Batch::STATUS_READY)
                $batch->updateStatus(\BoostMyShop\OrderPreparation\Model\Batch::STATUS_PRINTED);

            return $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
                'batch_' . $batch->getbob_type(). '_' . $date . '.pdf',
                $pdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('orderpreparation/preparation/index/');
        }

    }
}
