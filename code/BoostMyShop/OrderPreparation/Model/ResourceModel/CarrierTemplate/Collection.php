<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\CarrierTemplate', 'BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate');
    }

    public function addActiveFilter()
    {
        $this->getSelect()->where("ct_disabled = 0");

        return $this;
    }

    public function addShippingMethodFilter($shippingMethod)
    {
        $this->getSelect()->where("ct_shipping_methods like '%".$shippingMethod."%'");
        return $this;
    }

    public function addWarehouseFilter($warehouseId)
    {
        //handle following cases:
        // - warehouse id passed (we search it in the serialized string "x")
        // - wildcrad (all warehouses) "*"
        // - warehouse configuration empty or not done
        $this->getSelect()->where("((ct_warehouse_ids like '%\"".$warehouseId."\"%') or (ct_warehouse_ids like '%\"*\"%') or (ct_warehouse_ids is null) or (ct_warehouse_ids = 'a:0:{}'))");
        return $this;
    }
    public function addStoreFilter($storeId)
    {
        //handle following cases:
        // - store id passed (we search it in the serialized string "x")
        // - wildcrad (all warehouses) "*"
        // - store configuration empty or not done
        $this->getSelect()->where("((ct_store_ids like '%\"".$storeId."\"%') or (ct_store_ids like '%\"*\"%') or (ct_store_ids is null) or (ct_store_ids = 'a:0:{}'))");
        return $this;
    }


}
