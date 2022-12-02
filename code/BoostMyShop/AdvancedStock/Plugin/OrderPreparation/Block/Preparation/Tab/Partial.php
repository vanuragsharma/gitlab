<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Block\Preparation\Tab;

class Partial extends AbstractTab
{

    public function aroundAddAdditionnalFilters(\BoostMyShop\OrderPreparation\Block\Preparation\Tab\Partial $subject, $proceed, $collection)
    {
        $warehouseId = $subject->getWarehouseId();

        if ($subject->getManyOrdersMode() === false)
        {
            $partialOrderIds = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->joinOpenedOrder()->addProductTypeFilter()->addNotFullyReservedFilter()->addWarehouseFilter($warehouseId)->getOrderIds();
            $partialOrderIds[] = -1;
            if (count($partialOrderIds) > 0)
                $collection->addFieldToFilter('main_table.entity_id', array('in' => $partialOrderIds));

            $toShipOrderIds = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->joinOpenedOrder()->addProductTypeFilter()->addQtyReservedFilter()->addWarehouseFilter($warehouseId)->getOrderIds();
            $toShipOrderIds[] = -1;
            if (count($toShipOrderIds) > 0)
                $collection->addFieldToFilter('main_table.entity_id', array('in' => $toShipOrderIds));
        }
        else
        {
            $subquery = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->joinOpenedOrder()->addProductTypeFilter()->addNotFullyReservedFilter()->addWarehouseFilter($warehouseId);
            $subquery->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
            $subquery->getSelect()->columns(new \Zend_Db_Expr('distinct order_id'));
            $subquery = new \Zend_Db_Expr((string)$subquery->getSelect());
            $collection->addFieldToFilter('main_table.entity_id', array('in' => $subquery));

            $subqueryToShip = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->joinOpenedOrder()->addProductTypeFilter()->addQtyReservedFilter()->addWarehouseFilter($warehouseId);
            $subqueryToShip->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
            $subqueryToShip->getSelect()->columns(new \Zend_Db_Expr('distinct order_id'));
            $subqueryToShip = new \Zend_Db_Expr((string)$subqueryToShip->getSelect());
            $collection->addFieldToFilter('main_table.entity_id', array('in' => $subqueryToShip));
        }

    }

    public function aroundAddWarehouseFilter(\BoostMyShop\OrderPreparation\Block\Preparation\Tab\Partial $subject, $proceed, $collection, $warehouseId)
    {
        //$collection->addFieldToFilter('main_table.entity_id', ['in' => $this->getOpenedOrderIdForWarehouse($warehouseId)]);
    }

}
