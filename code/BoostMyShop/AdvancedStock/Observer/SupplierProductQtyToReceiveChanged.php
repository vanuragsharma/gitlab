<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SupplierProductQtyToReceiveChanged implements ObserverInterface
{
    protected $_warehouseItemFactory;
    protected $_warehouseCollectionFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory
    ) {
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
    }

    public function execute(EventObserver $observer)
    {
        $quantity = $observer->getEvent()->getquantity();
        $productId = $observer->getEvent()->getproduct_id();

        $warehouses = $this->_warehouseCollectionFactory
                        ->create()
                        ->addActiveFilter()
                        ->addFieldToFilter('w_sync_stock_from_po', ['eq' => 1]);

        if($warehouses->getSize()>0){
            foreach ($warehouses as $warehouse)
            {
                $warehouseItem = $this->_warehouseItemFactory
                                ->create()
                                ->loadByProductWarehouse($productId, $warehouse->getId());
                $warehouseItem->setwi_physical_quantity($quantity)
                                ->save();
            }
        }

        return $this;
    }

}
