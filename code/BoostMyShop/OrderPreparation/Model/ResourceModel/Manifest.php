<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel;


class Manifest extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_orderpreparation_manifest', 'bom_id');
    }
}
