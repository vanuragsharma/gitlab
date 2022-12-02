<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel\Manifest;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\Manifest', 'BoostMyShop\OrderPreparation\Model\ResourceModel\Manifest');
    }

    public function addWarehouseFilter($warehouseId)
    {
        $this->getSelect()->where('bom_warehouse_id = '.$warehouseId);
        return $this;
    }

    public function addActiveFilter()
    {
        $this->getSelect()->where('bom_edi_status != "'.\BoostMyShop\OrderPreparation\Model\Batch::STATUS_COMPLETE.'"');
        return $this;
    }

    public function addStatusFilter($status)
    {
        $this->getSelect()->where('bom_edi_status != "'.$status.'"');
        return $this;
    }

}