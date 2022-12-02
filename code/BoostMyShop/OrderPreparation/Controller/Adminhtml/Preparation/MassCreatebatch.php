<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class MassCreatebatch extends \Magento\Backend\App\AbstractAction
{
    protected $_batchHelper;
    protected $_config;
    protected $_inProgressCollectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \BoostMyShop\OrderPreparation\Model\BatchHelper $batchHelper,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressCollectionFactory
    )
    {
        parent::__construct($context);
        $this->_batchHelper = $batchHelper;
        $this->_config = $config;
        $this->_inProgressCollectionFactory = $inProgressCollectionFactory;
    }

    public function execute()
    {
        try {

            $carrier = null;
            $warehouseId = $this->getRequest()->getParam("wh_id");
            $type = $this->getRequest()->getParam("type");
            $maxOrdersCount = null;
            switch($type)
            {
                case 'unique':
                    $maxOrdersCount = $this->_config->maxOrdersCountInUniqueBatch();
                    break;
                case 'single':
                    $maxOrdersCount = $this->_config->maxOrdersCountInSingleBatch();
                    break;
                case 'multiple':
                    $maxOrdersCount = $this->_config->maxOrdersCountInMultipleBatch();
                    break;
            }

            $orderIds = $this->getRequest()->getPost('massaction');
            if (!is_array($orderIds))
                $orderIds = explode(',', $orderIds);

            //check if some orders are already in batches
            $this->checkOrdersAlreadyInBatch($warehouseId, $orderIds);

            $orderIdsToManage = array_slice($orderIds, 0, (int)$maxOrdersCount);
            $extraCount = count($orderIds) - count($orderIdsToManage);

            if(count($orderIds) > (int)$maxOrdersCount)
                $this->messageManager->addNotice(__('%1 orders could not been added to the batch as the max number of orders than can be added to a %2 batch is %3. Please create a new batch to manage remaining orders', $extraCount, $type, $maxOrdersCount));

            $batch = $this->_batchHelper->createNewBatch($warehouseId, $type, $carrier, $orderIdsToManage);

            $this->messageManager->addSuccess(__('Batch %1 / %2 successfully created', $batch->getbob_label(), $type));
            $this->_redirect('*/*/index');

        }catch(\Exception $e)
        {
            $this->messageManager->addError(__('%1', $e->getMessage()));
            $this->_redirect('*/*/index');
        }
    }

    protected function checkOrdersAlreadyInBatch($warehouseId, $orderIds)
    {
        $inProgress = $this->_inProgressCollectionFactory->create()
                                ->addWarehouseFilter($warehouseId)
                                ->addFieldToFilter('ip_order_id', ['in' => $orderIds])
                                ->addOrderDetails();
        if ($inProgress->getSize() > 0)
        {
            $inProgressOrderIncrementIds = [];
            foreach($inProgress as $item)
            {
                $inProgressOrderIncrementIds[] = $item->getincrement_id();
            }

            throw new \Exception(__('Batch not created, these orders are already in a batch : %1', implode(', ', $inProgressOrderIncrementIds)));
        }

        return true;
    }
}
