<?php

namespace BoostMyShop\OrderPreparation\Helper;

class Transaction extends \Magento\Framework\DB\Transaction
{

    public function reset()
    {
        $this->_objects = [];
        $this->_objectsByAlias = [];
        $this->_beforeCommitCallbacks = [];
    }

}