<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Barcodes;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\Barcodes', 'BoostMyShop\AdvancedStock\Model\ResourceModel\Barcodes');
    }

    public function addProductFilter($productId)
    {
        $this->getSelect()->where('bac_product_id = "'.$productId.'"');
        return $this;
    }

}
