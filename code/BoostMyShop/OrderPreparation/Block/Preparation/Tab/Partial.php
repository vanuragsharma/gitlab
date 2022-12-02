<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Partial extends \BoostMyShop\OrderPreparation\Block\Preparation\Tab
{

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('tab_partial');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    public function getAllowedOrderStatuses()
    {
        $statuses = $this->_config->create()->getOrderStatusesForTab('partial');
        return $statuses;
    }

    public function addAdditionnalFilters($collection)
    {

    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/partialAjaxGrid', ['_current' => true, 'grid' => 'partial']);
    }

    protected function _prepareColumns()
    {
        $this->_eventManager->dispatch('bms_order_preparation_partial_grid', ['grid' => $this]);

        $this->addExportType('*/*/exportpartialcsv', __('CSV'));

        return parent::_prepareColumns();
    }

    public function addExportType($url, $label)
    {
        $this->_exportTypes[] = new \Magento\Framework\DataObject(
            ['url' => $this->getUrl($url), 'label' => $label]
        );
        return $this;
    }
}
