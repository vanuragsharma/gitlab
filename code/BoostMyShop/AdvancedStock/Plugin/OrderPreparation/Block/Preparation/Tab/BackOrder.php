<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Block\Preparation\Tab;

class BackOrder extends AbstractTab
{

    public function aroundAddAdditionnalFilters(\BoostMyShop\OrderPreparation\Block\Preparation\Tab\BackOrder $subject, $proceed, $collection)
    {
        $warehouseId = $subject->getWarehouseId();

        if ($subject->getManyOrdersMode() === false)
        {
            $orderIdsWithReservation = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->joinOpenedOrder()->addProductTypeFilter()->addQtyToShipFilter()->addQtyReservedFilter()->addWarehouseFilter($warehouseId)->getOrderIds();
            $orderIdsWithReservation[] = -1;
            if (count($orderIdsWithReservation) > 0)
                $collection->addFieldToFilter('main_table.entity_id', array('nin' => $orderIdsWithReservation));
        }
        else
        {
            $query = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->joinOpenedOrder()->addProductTypeFilter()->addQtyToShipFilter()->addQtyReservedFilter()->addWarehouseFilter($warehouseId);
            $query->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(new \Zend_Db_Expr('distinct order_id'));
            $query = new \Zend_Db_Expr((string)$query->getSelect());

            $collection->addFieldToFilter('main_table.entity_id', array('nin' => $query));
        }

    }

    public function aroundAddWarehouseFilter(\BoostMyShop\OrderPreparation\Block\Preparation\Tab\BackOrder $subject, $proceed, $collection, $warehouseId)
    {
        $collection->addFieldToFilter('main_table.entity_id', ['in' => $this->getOpenedOrderIdForWarehouse($warehouseId, $subject->getManyOrdersMode())]);
    }

}
