<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress;


class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_orderpreparation_inprogress_item', 'ipi_id');
    }

    public function getIdFromOrderItemId($orderItemId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getTable('bms_orderpreparation_inprogress_item'),array('ipi_id'))
            ->where('ipi_order_item_id = "'.$orderItemId.'"');
        $ipiId = $connection->fetchOne($select);

        return $ipiId;
    }
}
