<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SalesOrderItemSaveAfter implements ObserverInterface
{
    protected $_orderFactory;
    protected $_orderItemFactory;
    protected $_orderItemCollectionFactory;
    protected $_extendedSalesFlatOrderItemFactory;
    protected $_router;
    protected $_logger;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ExtendedSalesFlatOrderItemFactory $extendedSalesFlatOrderItemFactory,
        \BoostMyShop\AdvancedStock\Model\Router $router,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->_extendedSalesFlatOrderItemFactory = $extendedSalesFlatOrderItemFactory;
        $this->_router = $router;
        $this->_logger = $logger;
    }

    public function execute(EventObserver $observer)
    {
        $orderItem = $observer->getEvent()->getItem();

        if (!$orderItem->getOrigData('item_id'))
        {
            //Insert records for extended sales order items (only if not exist)
            $extendedOrderItem = $this->_extendedSalesFlatOrderItemFactory->create()->loadByItemId($orderItem->getId());
            if (!$extendedOrderItem->getId()) {
                $this->_logger->log('Create extended order item record for item id #'.$orderItem->getId(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);
                $extendedOrderItem = $this->_extendedSalesFlatOrderItemFactory->create()->createFromOrderItem($orderItem->getOrder(), $orderItem);
                $warehouseId = ($extendedOrderItem->getesfoi_warehouse_id() ? $extendedOrderItem->getesfoi_warehouse_id() : 1);
                $this->_router->updateQuantityToShip($orderItem->getproduct_id(), $warehouseId);
            }
        }
        else
        {
            //If opened quantity changes
            if ($this->openedQuantityHasChanged($orderItem))
            {
                $this->_logger->log('Update quantities for order item aftersave #'.$orderItem->getId(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);

                $this->updateQuantities($orderItem);
                $this->processChild($orderItem);
            }
            else
                $this->_logger->log('Opened quantity has not changed for order item #'.$orderItem->getId(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);
        }

        return $this;
    }

    protected function updateQuantities($orderItem)
    {
        $extendedOrderItem = $this->_extendedSalesFlatOrderItemFactory->create()->loadByItemId($orderItem->getId());
        $extendedOrderItem->updateQtyToShip()->save();
        $warehouseId = ($extendedOrderItem->getesfoi_warehouse_id() ? $extendedOrderItem->getesfoi_warehouse_id() : 1);
        $this->_router->updateQuantityToShip($orderItem->getproduct_id(), $warehouseId);
        $this->_router->updateReservedQuantity($orderItem->getproduct_id(), $warehouseId);
    }

    protected function openedQuantityHasChanged($orderItem)
    {
        $fields = ['qty_ordered', 'qty_canceled', 'qty_refunded', 'qty_shipped'];
        foreach($fields as $field)
        {
            if ($orderItem->getOrigData($field) != $orderItem->getData($field))
                return true;
        }
        return false;
    }

    protected function processChild($parentItem)
    {
        switch($parentItem->getProductType())
        {
            case 'configurable':
                $this->_logger->log('Sync child order item for order item #'.$parentItem->getId(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);
                $childItem = $this->_orderItemFactory->create()->load($parentItem->getId(), 'parent_item_id');
                $this->updateQuantities($childItem);
                break;
            case 'bundle':
                if($parentItem->isShipSeparately())
                    return;
                $childItemCollection = $this->_orderItemCollectionFactory->create()->filterByParent($parentItem->getId());
                foreach ($childItemCollection as $childItem){
                    $this->updateQuantities($childItem);
                }
                break;
        }
    }
}
