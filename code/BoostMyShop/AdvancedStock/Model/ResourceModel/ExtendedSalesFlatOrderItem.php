<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;


class ExtendedSalesFlatOrderItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{


    protected function _construct()
    {
        $this->_init('bms_advancedstock_extended_sales_flat_order_item', 'esfoi_id');
    }

    public function forceReservedQty($id, $newReservedQty)
    {
        $tableName = $this->getTable('bms_advancedstock_extended_sales_flat_order_item');
        $sql = 'update '.$tableName.' set esfoi_qty_reserved = '.$newReservedQty.' where esfoi_id = '.$id;
        $this->getConnection()->query($sql);
    }

}
