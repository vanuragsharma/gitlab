<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Block\Preparation\Tab;

class InStock extends AbstractTab
{


    public function aroundAddAdditionnalFilters(\BoostMyShop\OrderPreparation\Block\Preparation\Tab\InStock $subject, $proceed, $collection)
    {
        $warehouseId = $subject->getWarehouseId();

        if ($subject->getManyOrdersMode() === false)
        {
            $backOrderIds = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->joinOpenedOrder()->addProductTypeFilter()->addQtyToShipFilter()->addNotFullyReservedFilter()->addWarehouseFilter($warehouseId)->getOrderIds();
            if (count($backOrderIds) > 0)
                $collection->addFieldToFilter('main_table.entity_id', array('nin' => $backOrderIds));

            $toShipOrderIds = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->joinOpenedOrder()->addProductTypeFilter()->addQtyToShipFilter()->addWarehouseFilter($warehouseId)->getOrderIds();
            $toShipOrderIds[] = -1;
            if (count($toShipOrderIds) > 0)
                $collection->addFieldToFilter('main_table.entity_id', array('in' => $toShipOrderIds));
        }
        else
        {
            //use subqueries for many orders mode

            $subqueryBackorders = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->joinOpenedOrder()->addProductTypeFilter()->addQtyToShipFilter()->addNotFullyReservedFilter()->addWarehouseFilter($warehouseId);
            $subqueryBackorders->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
            $subqueryBackorders->getSelect()->columns(new \Zend_Db_Expr('distinct order_id'));
            $subqueryBackorders = new \Zend_Db_Expr((string)$subqueryBackorders->getSelect());
            $collection->addFieldToFilter('main_table.entity_id', array('nin' => $subqueryBackorders));

            $subquery = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->joinOpenedOrder()->addProductTypeFilter()->addQtyToShipFilter()->addWarehouseFilter($warehouseId);
            $subquery->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
            $subquery->getSelect()->columns(new \Zend_Db_Expr('distinct order_id'));
            $subquery = new \Zend_Db_Expr((string)$subquery->getSelect());
            $collection->addFieldToFilter('main_table.entity_id', array('in' => $subquery));
        }

    }


    public function aroundAddWarehouseFilter(\BoostMyShop\OrderPreparation\Block\Preparation\Tab\InStock $subject, $proceed, $collection, $warehouseId)
    {
        //$collection->addFieldToFilter('main_table.entity_id', ['in' => $this->getOpenedOrderIdForWarehouse($warehouseId)]);
    }

}
