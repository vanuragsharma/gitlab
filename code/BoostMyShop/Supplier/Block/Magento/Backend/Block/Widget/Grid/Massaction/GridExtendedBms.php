<?php

namespace BoostMyShop\Supplier\Block\Magento\Backend\Block\Widget\Grid\Massaction;

class GridExtendedBms extends \Magento\Backend\Block\Widget\Grid\Massaction\Extended
{

    /*
    * Method is overwritten due to Magento issue (it is impossible to select all items in grid)
    * Magento feature which allows to getGridIds() by custom massAction column (getMassactionIdField) is not supported
    * @return string
    */
    public function getGridIdsJson()
    {
        if (!$this->getUseSelectAll()) {
            return '';
        }

        /** @var \Magento\Framework\Data\Collection $allIdsCollection */
        $allIdsCollection = clone $this->getParentBlock()->getCollection();
        $gridIds = $allIdsCollection->clear()->setPageSize(0)->getAllIds();

        if (!empty($gridIds)) {
            return join(",", $gridIds);
        }

        return '';
    }

}