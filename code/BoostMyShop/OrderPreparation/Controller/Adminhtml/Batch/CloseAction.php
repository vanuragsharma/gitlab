<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Batch;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class CloseAction extends \Magento\Backend\App\AbstractAction
{
    protected $_orderFactory;
    protected $_batchFactory;
    protected $_batchHelper;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \BoostMyShop\OrderPreparation\Model\BatchFactory $batchFactory,
        \BoostMyShop\OrderPreparation\Model\BatchHelper $batchHelper
    ){
        parent::__construct($context);
        $this->_orderFactory = $orderFactory;
        $this->_batchFactory = $batchFactory;
        $this->_batchHelper = $batchHelper;
    }

    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        try {
            $bob_id = $this->getRequest()->getParam('bob_id');
            if (!$bob_id)
                throw new \Exception(__('No batch ID provided'));

            $batch = $this->_batchFactory->create()->load($bob_id);
            if(!$batch)
                throw new \Exception(__('No batch found with ID %1', $bob_id));

            $batchOrders = $batch->getBatchOrders();

            $inProgressIdsToRemove = [];
            $orderReferencesToRemove = [];
            foreach($batchOrders as $inProgress)
            {
                if($inProgress->getip_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PACKED)
                {
                    throw new \Exception(
                        __("Impossible to close batch %1 as it contains an order having the preparation status 'packed' (ID : %2)", $batch->getbob_label(), $inProgress->getip_order_id())
                        .'<br/>'.
                        __("Please add a tracking number to this order to change its preparation status to 'shipped'.")
                    );
                }
                if($inProgress->getip_status() != \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED)
                {
                    $inProgressIdsToRemove[] = $inProgress->getip_id();
                    $order = $this->_orderFactory->create()->load($inProgress->getip_order_id());
                    $orderReferencesToRemove[] = $order->getincrement_id() ? : $inProgress->getip_order_id();
                }
            }

            $title = __('Order(s) removed');
            $comments = __('Order(s) : #%1 have been removed from batch as it was closed when they were not shipped yet', implode(', #', $orderReferencesToRemove));
            foreach($inProgressIdsToRemove as $inProgressId)
            {
                $batch->removeOrder($inProgressId);
            }

            $batch->addOrganizer($title, $comments);

            if($batch->getbob_order_count() > 0)
                $batch->markAsComplete();
            else
                $batch->delete();

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->_redirect('orderpreparation/preparation/index/');
    }
}
