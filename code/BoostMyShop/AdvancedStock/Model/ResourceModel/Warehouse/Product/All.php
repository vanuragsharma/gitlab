<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Product;


class All extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    protected $_usePrimarySupplierCost = false;

    public function setUsePrimarySupplierCost($value)
    {
        $this->_usePrimarySupplierCost = $value;
    }

    public function load($printQuery = false, $logQuery = false)
    {
        $this->addAttributeToSelect('name');
        $this->addAttributeToSelect('cost');

        $this->addFieldToFilter('type_id', array('in' => array('simple')));


        parent::load($printQuery, $logQuery);

        return $this;
    }

    public function isSupplierProductTableExists()
    {
        $tableName = $this->getTable('bms_supplier_product');
        return $this->getConnection()->isTableExists($tableName);
    }

    public function addRowValue()
    {
        if ($this->isSupplierProductTableExists())
        {
            $this->getSelect()->joinLeft(
                $this->getTable('bms_supplier_product'),
                'sp_product_id = e.entity_id and sp_primary = 1',
                ['sp_price', 'sp_pack_qty']
            );

            $this->getSelect()->joinLeft(
                $this->getTable('bms_supplier'),
                'sp_sup_id = sup_id',
                ['sup_currency']
            );
        }

        if ($this->isSupplierProductTableExists())
            $this->addExpressionAttributeToSelect('total_row_value', 'concat(if ({{cost}} > 0, {{cost}}, sp_price) * wi_physical_quantity)', ['cost']);
        else
            $this->addExpressionAttributeToSelect('total_row_value', 'concat({{cost}} * wi_physical_quantity)', ['cost']);

        return $this;
    }

    public function getTotalValue()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        if ($this->isSupplierProductTableExists()) {

            $this->getSelect()->joinLeft(
                $this->getTable('bms_supplier_product'),
                'sp_product_id = e.entity_id and sp_primary = 1',
                ['sp_price', 'sp_pack_qty']
            );

            $this->addExpressionAttributeToSelect('total_value', 'SUM(if ({{cost}} > 0, {{cost}}, sp_price) * wi_physical_quantity)', ['cost']);
        }
        else
            $this->addExpressionAttributeToSelect('total_value', 'SUM({{cost}} * wi_physical_quantity)', ['cost']);
        $result = $this->getConnection()->fetchRow($this->getSelect());
        return (isset($result['total_value']) ? $result['total_value'] : 0);
    }

    public function addWarehouseFilter($warehouseId)
    {
        $condition =  '	wi_product_id = e.entity_id';
        $condition .= ' and wi_warehouse_id = '.$warehouseId;

        $this->getSelect()->join(
            $this->getTable('bms_advancedstock_warehouse_item'),
            $condition
        );

        return $this;
    }

    public function setOrder($attribute, $dir = 'DESC')
    {
        switch ($attribute) {
            case 'wi_physical_quantity':
            case 'wi_available_quantity':
            case 'wi_quantity_to_ship':
            case 'wi_shelf_location':
                $this->getSelect()->order($attribute . ' ' . $dir);
                break;
            default:
                parent::setOrder($attribute, $dir);
                break;
        }
        return $this;
    }

    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        switch ($attribute) {
            case 'wi_physical_quantity':
            case 'wi_available_quantity':
            case 'wi_quantity_to_ship':
            case 'wi_shelf_location':
                $conditionSql = $this->_getConditionSql($attribute, $condition);
                $this->getSelect()->where($conditionSql);
                break;
            default:
                parent::addAttributeToFilter($attribute, $condition, $joinType);
                break;
        }
        return $this;
    }

}
