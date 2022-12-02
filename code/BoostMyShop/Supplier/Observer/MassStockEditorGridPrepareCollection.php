<?php

namespace BoostMyShop\Supplier\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class MassStockEditorGridPrepareCollection implements ObserverInterface
{

    public function execute(EventObserver $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        $collection->addAttributeToSelect('supply_discontinued');
        return $collection;
    }
}

