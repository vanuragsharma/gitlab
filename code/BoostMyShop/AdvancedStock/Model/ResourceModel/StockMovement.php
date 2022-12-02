<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;


class StockMovement extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_advancedstock_stock_movement', 'sm_id');
    }

    public function getDisabledStockMovementWarehouseIds()
    {
        $tableName = $this->getTable('bms_advancedstock_warehouse');
        $sql = 'select w_id from '.$tableName.' where w_disable_stock_movement = 1';
        $result = $this->getConnection()->fetchCol($sql);
        return $result;
    }

}
