<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel;


class Batch extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_orderpreparation_batch', 'bob_id');
    }
}
