<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Holded extends \BoostMyShop\OrderPreparation\Block\Preparation\Tab
{

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('tab_holded');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    public function getAllowedOrderStatuses()
    {
        return $this->_config->create()->getOrderStatusesForTab('holded');
    }

    public function addAdditionnalFilters($collection)
    {

    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/holdedAjaxGrid', ['_current' => true, 'grid' => 'holded']);
    }

    protected function _prepareColumns()
    {
        $this->_eventManager->dispatch('bms_order_preparation_holded_grid', ['grid' => $this]);

        $this->addExportType('*/*/exportholdedcsv', __('CSV'));

        return parent::_prepareColumns();
    }

    public function addExportType($url, $label)
    {
        $this->_exportTypes[] = new \Magento\Framework\DataObject(
            ['url' => $this->getUrl($url), 'label' => $label]
        );
        return $this;
    }

    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        if($this->_config->create()->isBatchEnable()){
            $this->getMassactionBlock()->addItem(
                'unholdshippinglabel',
                [
                    'label' => __('Unhold ShippingLabel'),
                    'url' => $this->getUrl('*/*/massUnholdShippinglabel', ['_current' => true])
                ]
            );
        }
    }
}
