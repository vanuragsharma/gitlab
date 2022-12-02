<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Order;


class History extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('bms_purchase_order_history', 'poh_id');
    }
}
