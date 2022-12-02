<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation;

class BatchPrinted extends \BoostMyShop\OrderPreparation\Block\Batch\Grid
{
    protected function _construct()
    {
        parent::_construct();

        $this->setId('tab_batch_printed');
        $this->setDefaultSort('bob_id');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
    }

    public function addAdditionalFilters($collection)
    {
        $collection->addFieldToFilter('bob_status', array('in' => [
            \BoostMyShop\OrderPreparation\Model\Batch::STATUS_PRINTED,
            \BoostMyShop\OrderPreparation\Model\Batch::STATUS_INPROGRESS
        ]));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/batchPrintedAjaxGrid', ['_current' => true, 'grid' => 'selected']);
    }
}
