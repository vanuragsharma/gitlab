<?php
namespace BoostMyShop\Supplier\Controller\Adminhtml\Replenishment;

use Magento\Framework\Controller\ResultFactory;

class ChangeWarehouse extends \Magento\Backend\App\Action
{
    protected $_replenishmentRegistry;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \BoostMyShop\Supplier\Model\Registry $replenishmentRegistry
    ) {
        parent::__construct($context);
        $this->_replenishmentRegistry = $replenishmentRegistry;
    }
    public function execute()
    {
        $warehouseId = $this->getRequest()->getParam('warehouse_id');
        $this->_replenishmentRegistry->setCurrentWarehouseId($warehouseId);
        $this->messageManager->addSuccess(__('Warehouse updated'));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/Index');
    }
    protected function _isAllowed()
    {
        return true;
    }
}