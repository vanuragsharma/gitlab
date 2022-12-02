<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;

class Barcodes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('bms_advancedstock_barcodes', 'bac_id');
    }

    public function getBarcodesFromProductId($id)
    {
        $select = $this->getConnection()
            ->select()
            ->from(["bac" => $this->getTable('bms_advancedstock_barcodes')],['*'])
            ->where('bac.`bac_product_id` = "' .$id.'"');
        return $this->getConnection()->fetchAll($select);
    }
}