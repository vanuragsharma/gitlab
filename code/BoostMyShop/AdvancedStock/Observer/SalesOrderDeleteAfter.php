<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SalesOrderDeleteAfter implements ObserverInterface
{
    protected $_extendedSalesFlatOrderItemFactory;
    protected $_router;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ExtendedSalesFlatOrderItemFactory $extendedSalesFlatOrderItemFactory,
        \BoostMyShop\AdvancedStock\Model\Router $router
    ) {
        $this->_extendedSalesFlatOrderItemFactory = $extendedSalesFlatOrderItemFactory;
        $this->_router = $router;
    }

    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();

        foreach ($order->getData('delete_order_items') as $element)
        {
            $itemId = $element['item_id'];
            $productId = $element['product_id'];

            $extendedItem = $this->_extendedSalesFlatOrderItemFactory->create()->loadByItemId($itemId);
            $warehouseId = $extendedItem->getesfoi_warehouse_id();

            $extendedItem->delete();

            if ($warehouseId)
            {
                $this->_router->updateQuantityToShip($productId, $warehouseId);
                $this->_router->updateReservedQuantity($productId, $warehouseId);
            }

        }

    }

}
