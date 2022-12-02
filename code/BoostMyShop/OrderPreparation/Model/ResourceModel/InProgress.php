<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel;


class InProgress extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_orderpreparation_inprogress', 'ip_id');
    }

    public function getIdFromShipmentReference($shipmentReference)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getTable('sales_shipment'),array('entity_id'))
            ->where('increment_id = "'.$shipmentReference.'"');
        $shipmentId = $connection->fetchOne($select);
        if (!$shipmentId)
            throw new \Exception('Unable to load shipment #'.$shipmentReference);

        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable(), array('ip_id'))
            ->where('ip_shipment_id = '.$shipmentId);
        $ipId = $connection->fetchOne($select);

        return $ipId;
    }

    public function getIdFromOrderReference($orderReference)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getTable('sales_order'), array('entity_id'))
            ->where('increment_id = "'.$orderReference.'"');
        $orderId = $connection->fetchOne($select);
        if (!$orderId)
            throw new \Exception('Unable to load order #'.$orderReference);

        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable(), array('ip_id'))
            ->where('ip_order_id = '.$orderId);
        $ipId = $connection->fetchOne($select);

        return $ipId;
    }

    public function getIdFromOrderIdAndContext($orderId, $warehouseId, $operatorId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable(), array('ip_id'))
            ->where('ip_order_id = '.$orderId);
        
        //API calls : don't apply filter on warehouse & operator
        if($operatorId == -1){
            $ipId = $connection->fetchOne($select);
            return $ipId;
        }
        
        $select->where('ip_warehouse_id = '.$warehouseId);
        if ($operatorId)
            $select->where('ip_user_id = '.$operatorId);

        $ipId = $connection->fetchOne($select);

        return $ipId;
    }

    public function isOrderAlreadyAdded($orderId, $warehouseId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable(), array('ip_id'))
            ->where('ip_order_id = '.$orderId)
            ->where('ip_warehouse_id = '.$warehouseId);
        $ipId = $connection->fetchOne($select);
        return ($ipId > 0);
    }

}
