<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;

class SalesHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('bms_advancedstock_sales_history', 'sh_id');
    }

    public function updateForProductWarehouse($warehouseItem, $ranges, $qtyToUse = 'qty_invoiced')
    {
        $connection = $this->getConnection();

        $connection->delete($this->getMainTable(), 'sh_warehouse_item_id='.$warehouseItem->getId());

        $data = ['sh_warehouse_item_id' => $warehouseItem->getId(), 'sh_range_1' => 0, 'sh_range_2' => 0, 'sh_range_3' => 0];

        $fromDate = array();
        for($i=1;$i<=3;$i++)
        {
            $fromDate[$i] = date('Y-m-d', time() - (3600 * 24 * 7) * $ranges[$i - 1]);
        }

        $qtyInvoicedByRange = $this->calculateHistory($connection, $warehouseItem->getId(), $fromDate, $qtyToUse);

        for($i=1;$i<=3;$i++)
        {
            $data['sh_range_'.$i] = (int) $qtyInvoicedByRange['range_'.$i];
        }

        $lastSmData = $this->calculateLastStockMovementData($connection, $warehouseItem);
        $data['sh_last_sm_date'] = $lastSmData ? $lastSmData['sm_created_at'] : NULL;
        $data['sh_last_sm_qty'] = $lastSmData ? $lastSmData['sm_qty'] : NULL;

        $lastOrderData = $this->calculateLastOrderData($connection, $warehouseItem);
        $data['sh_last_order_date'] = $lastOrderData ? $lastOrderData['created_at'] : NULL;

        $this->getConnection()->insert($this->getMainTable(), $data);

        return $this;
    }

    public function calculateHistory($connection, $warehouseItemId, $fromDate, $qtyToUse)
    {
        $sql = "SELECT
                    SUM(CASE WHEN created_at >= '".$fromDate[1]."' THEN ".$qtyToUse." ELSE 0 END) AS range_1,
                    SUM(CASE WHEN created_at >= '".$fromDate[2]."' THEN ".$qtyToUse." ELSE 0 END) AS range_2,
                    SUM(CASE WHEN created_at >= '".$fromDate[3]."' THEN ".$qtyToUse." ELSE 0 END) AS range_3
                FROM ".$this->getTable('sales_order_item')." 
                    JOIN ".$this->getTable('bms_advancedstock_extended_sales_flat_order_item')." ON item_id = esfoi_order_item_id
                    JOIN ".$this->getTable('bms_advancedstock_warehouse_item')." ON esfoi_warehouse_id = wi_warehouse_id AND product_id = wi_product_id
                WHERE wi_id = " . $warehouseItemId;

        $result = $connection->fetchAll($sql);

        return $result[0];
    }

    public function calculateLastStockMovementData($connection, $warehouseItem)
    {
        $categories = [
            \BoostMyShop\AdvancedStock\Model\StockMovement\Category::purchaseOrder,
            \BoostMyShop\AdvancedStock\Model\StockMovement\Category::transfer
        ];

        $warehouseItemTable = $this->getTable('bms_advancedstock_warehouse_item');
        $stockMovementTable = $this->getTable('bms_advancedstock_stock_movement');

        $sql = "SELECT 
                    sm.sm_created_at,
                    sm.sm_qty
                FROM ".$warehouseItemTable."
                    JOIN ".$stockMovementTable." AS sm ON sm_product_id = wi_product_id 
                    WHERE wi_id = ".$warehouseItem->getId()." 
                    AND sm_id =
                    (
                        SELECT MAX(sm2.sm_id) FROM ".$warehouseItemTable."
                            JOIN ".$stockMovementTable." AS sm2 ON sm_product_id = wi_product_id  
                                AND sm2.sm_to_warehouse_id = ".$warehouseItem->getData('wi_warehouse_id')."
                                AND sm2.sm_category IN (".implode(',', $categories).")
                                AND wi_id = ".$warehouseItem->getId()."
                    )";

        $result = $connection->fetchAll($sql);

        return isset($result[0]) ? $result[0] : null;
    }

    public function calculateLastOrderData($connection, $warehouseItem)
    {
        $sql = "SELECT 
                    MAX(so.created_at) as created_at
                FROM ".$this->getTable('bms_advancedstock_warehouse_item')."
                    JOIN ".$this->getTable('sales_order_item')." AS soi ON soi.product_id = wi_product_id 
                    JOIN ".$this->getTable('sales_order')." AS so ON so.entity_id = soi.order_id
                    JOIN ".$this->getTable('bms_advancedstock_extended_sales_flat_order_item')." AS esfoi ON esfoi.esfoi_order_item_id = soi.item_id AND esfoi.esfoi_warehouse_id = ".$warehouseItem->getData('wi_warehouse_id')."
                WHERE wi_id = ".$warehouseItem->getId();

        $result = $connection->fetchAll($sql);

        return isset($result[0]) ? $result[0] : [];
    }
}
