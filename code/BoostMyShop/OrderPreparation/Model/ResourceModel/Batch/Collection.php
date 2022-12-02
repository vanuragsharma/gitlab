<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel\Batch;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\Batch', 'BoostMyShop\OrderPreparation\Model\ResourceModel\Batch');
    }

    public function addWarehouseFilter($warehouseId)
    {
        $this->getSelect()->where('bob_warehouse_id = '.$warehouseId);
        return $this;
    }

    public function addActiveFilter()
    {
        $this->getSelect()->where('bob_status != "'.\BoostMyShop\OrderPreparation\Model\Batch::STATUS_COMPLETE.'"');
        return $this;
    }

    public function addStatusFilter($status)
    {
        $this->getSelect()->where('bob_status = "'.$status.'"');
        return $this;
    }

}
