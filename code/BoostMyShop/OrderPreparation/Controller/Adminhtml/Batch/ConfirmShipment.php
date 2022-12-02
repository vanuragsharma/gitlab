<?php
namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Batch;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ConfirmShipment extends \Magento\Backend\App\AbstractAction
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

            $carrier = null;
            $batchId = $this->getRequest()->getParam("bob_id");
            if (!$batchId) {
                throw new \Exception(__("No batch id found"));
            }

            $batch = $this->_batchFactory->create()->load($batchId);

            $batchOrders = $batch->getBatchOrders();
            foreach ($batchOrders as $order)
            {
                try {
                    if ($order->getOrder()->canShip()) {
                        $order->pack($this->_config->getCreateShipment(), $this->_config->getCreateInvoice(), null, $order->getip_total_weight());
                        $batch->updatedProgress("shipment");
                    }
                }
                catch(\Exception $e)
                {
                    //$this->messageManager->addError(__('Error while creating shipment for order #%1', $order->getOrder()->getIncrementId()));
                }
            }
            $batch->updatedProgress("shipment");
            $batch->markAsComplete();

            $this->messageManager->addSuccess(__('Shipment Confirm for Batch %1/%2 successfully', $batch->getbob_label(), $batch->getbob_type()));
            $this->_redirect('orderpreparation/preparation/index/');

        }catch(\Exception $e)
        {
            $this->messageManager->addError(__('%1', $e->getMessage()));
            $this->_redirect('orderpreparation/preparation/index');
        }
    }
}
