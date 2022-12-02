<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Block\Preparation\Tab;

class AbstractTab
{
    protected $_extendedOrderItemCollectionFactory;

    protected static $_cache;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\ExtendedSalesFlatOrderItem\CollectionFactory $extendedOrderItemCollectionFactory
    )
    {
        $this->_extendedOrderItemCollectionFactory = $extendedOrderItemCollectionFactory;
    }

    protected function getOpenedOrderIdForWarehouse($warehouseId, $manyOrdersMode)
    {
        $cacheKey = 'getOpenedOrderIdForWarehouse_'.$warehouseId;

        if (!isset(self::$_cache[$cacheKey]))
        {
            if ($manyOrdersMode === false)
            {
                self::$_cache[$cacheKey] = $this->_extendedOrderItemCollectionFactory->create()->addQtyToShipFilter()->joinOrderItem()->joinOpenedOrder()->addWarehouseFilter($warehouseId)->addProductTypeFilter()->getOrderIds();
            }
            else
            {
                $query = $this->_extendedOrderItemCollectionFactory->create()->addQtyToShipFilter()->joinOrderItem()->joinOpenedOrder()->addWarehouseFilter($warehouseId)->addProductTypeFilter();
                $query->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(new \Zend_Db_Expr('distinct order_id'));
                $query = new \Zend_Db_Expr((string)$query->getSelect());
                self::$_cache[$cacheKey] = $query;
            }
        }

        return self::$_cache[$cacheKey];
    }

}
