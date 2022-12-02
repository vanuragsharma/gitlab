<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\StockMovement;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    protected $_stockMovementTable;

    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Product', 'Magento\Catalog\Model\ResourceModel\Product');
        $this->setRowIdFieldName('sm_id');
        $this->_stockMovementTable = $this->_resource->getTableName('bms_advancedstock_stock_movement');
        $this->_initTables();
    }

    /**
     * Initialize select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinFields();
        return $this;
    }

    /**
     * Join fields to entity
     *
     * @return $this
     */
    protected function _joinFields()
    {
        $this->addAttributeToSelect('name')->addAttributeToSelect('sku');

        $this->getSelect()->join(
            ['sm' => $this->_stockMovementTable],
            'sm_product_id = e.entity_id',
            ['*']
        );
        return $this;
    }

    /**
     * Set order to attribute
     *
     * @param string $attributea
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = 'DESC')
    {
        switch ($attribute) {
            case 'sm_id':
            case 'sm_created_at':
            case 'sm_product_id':
            case 'sm_from_warehouse_id':
            case 'sm_to_warehouse_id':
            case 'sm_qty':
            case 'sm_category':
            case 'sm_comments':
            case 'sm_user_id':
            case 'sm_ui':
            case 'sm_parent_id':
            case 'sm_new_qty':
                $this->getSelect()->order($attribute . ' ' . $dir);
                break;
            default:
                parent::setOrder($attribute, $dir);
                break;
        }
        return $this;
    }

    /**
     * Add attribute to filter
     *
     * @param AbstractAttribute|string $attribute
     * @param array|null $condition
     * @param string $joinType
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        switch ($attribute) {
            case 'sm_id':
            case 'sm_created_at':
            case 'sm_product_id':
            case 'sm_from_warehouse_id':
            case 'sm_to_warehouse_id':
            case 'sm_qty':
            case 'sm_category':
            case 'sm_comments':
            case 'sm_user_id':
            case 'sm_ui':
            case 'sm_parent_id':
            case 'sm_new_qty':
                $conditionSql = $this->_getConditionSql($attribute, $condition);
                $this->getSelect()->where($conditionSql);
                break;
            default:
                parent::addAttributeToFilter($attribute, $condition, $joinType);
                break;
        }
        return $this;
    }

    public function addProductFilter($product)
    {
        if (is_object($product)) {
            $product = $product->getId();
        }
        $this->getSelect()->where('sm_product_id = '.$product);
        return $this;
    }

    public function addWarehouseFilter($warehouseId)
    {
        $this->getSelect()->where('sm_from_warehouse_id = '.$warehouseId.' OR sm_to_warehouse_id = '.$warehouseId);
        return $this;
    }

    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = $this->_getClearSelect();
        $countSelect->columns('COUNT(DISTINCT sm_id)');
        return $countSelect;
    }

    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('sm.sm_id');
        $idsSelect->limit($limit, $offset);
        //$idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

}
