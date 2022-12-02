<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SalesOrderDeleteBefore implements ObserverInterface
{

    //store items in order data to process them in deleteAfter event
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $itemIds = [];
        foreach($order->getItems() as $item)
        {
            $itemIds[] = ['item_id' => $item->getId(), 'product_id' => $item->getproduct_id()];
        }

        $order->setData('delete_order_items', $itemIds);
    }

}
