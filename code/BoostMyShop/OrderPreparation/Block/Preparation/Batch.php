<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation;

class Batch extends \BoostMyShop\OrderPreparation\Block\Batch\Grid
{
    protected function _construct()
    {
        parent::_construct();

        $this->setId('tab_batch');
        $this->setDefaultSort('bob_id');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
    }

    public function addAdditionalFilters($collection)
    {
        $collection->addActiveFilter();
        $collection->addFieldToFilter('bob_status', array('in' => [
            \BoostMyShop\OrderPreparation\Model\Batch::STATUS_NEW,
            \BoostMyShop\OrderPreparation\Model\Batch::STATUS_READY_FOR_LABEL_GENERATION,
            \BoostMyShop\OrderPreparation\Model\Batch::STATUS_SHIPPING_LABEL_GENERATION,
            \BoostMyShop\OrderPreparation\Model\Batch::STATUS_READY
        ]));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/batchAjaxGrid', ['_current' => true, 'grid' => 'selected']);
    }
}
