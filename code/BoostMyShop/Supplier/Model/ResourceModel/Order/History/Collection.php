<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Order\History;


/**
 * Class Collection
 * @package BoostMyShop\Supplier\Model\ResourceModel\Order\History
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\Order\History', 'BoostMyShop\Supplier\Model\ResourceModel\Order\History');
    }

    /**
     * @param $orderId
     * @return $this
     */
    public function addPoFilter($orderId)
    {
        $this->getSelect()->where("poh_po_id = ".$orderId);
        return $this;
    }

}
