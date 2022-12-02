<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;


class Warehouse extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_advancedstock_warehouse', 'w_id');
    }

    public function getSkuCount($warehouseId)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('bms_advancedstock_warehouse_item'), array(new \Zend_Db_Expr('COUNT(DISTINCT wi_product_id) as total')))
            ->join(['cp' => $this->getTable('catalog_product_entity')], 'cp.entity_id = wi_product_id')
            ->where('wi_warehouse_id = ' .$warehouseId)
            ->where('cp.type_id = "simple"')
            ->where('wi_physical_quantity > 0');
        $result = $this->getConnection()->fetchOne($select);
        if (!$result)
            $result = 0;
        return $result;
    }

    public function getProductsCount($warehouseId)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('bms_advancedstock_warehouse_item'), array(new \Zend_Db_Expr('SUM(wi_physical_quantity) as total')))
            ->join(['cp' => $this->getTable('catalog_product_entity')], 'cp.entity_id = wi_product_id')
            ->where('wi_warehouse_id = ' .$warehouseId)
            ->where('cp.type_id = "simple"');
        $result = $this->getConnection()->fetchOne($select);
        if (!$result)
            $result = 0;
        return $result;
    }

}
