<?php
namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Batch;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class RemoveOrder extends \Magento\Backend\App\AbstractAction
{
    protected $_orderFactory;
    protected $_batchFactory;
    protected $_inProgressFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \BoostMyShop\OrderPreparation\Model\BatchFactory $batchFactory,
        \BoostMyShop\OrderPreparation\Model\InProgressFactory $inProgressFactory
    )
    {
        parent::__construct($context);

        $this->_orderFactory = $orderFactory;
        $this->_batchFactory = $batchFactory;
        $this->_inProgressFactory = $inProgressFactory;
    }

    public function execute()
    {
        try
        {
            $batchId = $this->getRequest()->getParam("bob_id");
            if (!$batchId) {
                throw new \Exception(__("No batch id found"));
            }

            $ipId = $this->getRequest()->getParam("ip_id");
            if (!$ipId) {
                throw new \Exception(__("No order id found"));
            }

            $inProgress = $this->_inProgressFactory->create()->load($ipId);
            $order = $this->_orderFactory->create()->load($inProgress->getip_order_id());
            $orderReferenceToRemove = $order->getincrement_id() ? : $inProgress->getip_order_id();

            //remove order from batch
            $batch = $this->_batchFactory->create()->load($batchId);
            $batch->removeOrder($ipId);

            //log order remove in batch
            $title = __('Order removed');
            $comments = __('Order #%1 have been removed from batch', $orderReferenceToRemove);
            $batch->addOrganizer($title, $comments);

            $this->messageManager->addSuccess(__('Order removed from batch'));
        }
        catch(\Exception $e)
        {
            $this->messageManager->addError(__('%1', $e->getMessage()));
        }

        $this->_redirect('orderpreparation/preparation/index/');
    }
}