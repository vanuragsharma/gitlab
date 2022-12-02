<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\Item;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\InProgress\Item', 'BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\Item');
    }

    public function addOrderFilter($orderId)
    {
        $this->getSelect()->where("ipi_order_id = ".$orderId);
        return $this;
    }

    public function addParentFilter($inProgressId)
    {
        $this->getSelect()->where("ipi_parent_id= ".$inProgressId);
        return $this;
    }

    public function joinOrderItem()
    {
        $this->getSelect()->join($this->getTable('sales_order_item'), 'ipi_order_item_id = item_id');
        return $this;
    }

    public function joinInProgress()
    {
        $this->getSelect()->join($this->getTable('bms_orderpreparation_inprogress'), 'ip_id = ipi_parent_id');
        return $this;
    }

    public function joinOrder()
    {
        $this->getSelect()->join(['so' => $this->getTable('sales_order')], 'order_id = so.entity_id');
        return $this;
    }

    public function deleteForParent($inProgressId)
    {
        $this->getConnection()->delete($this->getTable('bms_orderpreparation_inprogress_item'), 'ipi_parent_id = '.$inProgressId);
        return $this;
    }

    public function addSearchProductFilter($value)
    {
        $value = addslashes($value);
        $this->getSelect()->where('((sku like "%'.$value.'%") OR (name like "%'.$value.'%"))');
        return $this;
    }

    public function getOrderIds()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns(new \Zend_Db_Expr('distinct ipi_order_id'));
        return $this->getConnection()->fetchCol($this->getSelect());
    }

    public function joinBarcode($barcodeAttributeCode)
    {
        $this->getSelect()->joinLeft(['cpev' => $this->getTable('catalog_product_entity_varchar')], 'cpev.entity_id = product_id and cpev.store_id = 0');
        $this->getSelect()->join(['ea' => $this->getTable('eav_attribute')], 'cpev.attribute_id = ea.attribute_id and attribute_code = "'.$barcodeAttributeCode.'"');

        return $this;
    }

}
