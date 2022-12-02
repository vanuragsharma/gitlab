<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\StockDiscrepencies;


class MissingWarehouseItems extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('', '');
    }


    public function getExisting()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('bms_advancedstock_warehouse_item'), array(new \Zend_Db_Expr('distinct concat(wi_warehouse_id, "_", wi_product_id) as item')));
        $result = $this->getConnection()->fetchCol($select);
        return $result;
    }

    public function getRequired()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('bms_advancedstock_warehouse'), [])
            ->from($this->getTable('catalog_product_entity'), array(new \Zend_Db_Expr('distinct concat(w_id, "_", entity_id) as item')));
        $result = $this->getConnection()->fetchCol($select);
        return $result;
    }

}
