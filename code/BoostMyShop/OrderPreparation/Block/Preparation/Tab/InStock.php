<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class InStock extends \BoostMyShop\OrderPreparation\Block\Preparation\Tab
{

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('tab_stock');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    public function getAllowedOrderStatuses()
    {
        return $this->_config->create()->getOrderStatusesForTab('instock');
    }

    public function addAdditionnalFilters($collection)
    {

    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/instockAjaxGrid', ['_current' => true, 'grid' => 'instock']);
    }


    protected function _prepareColumns()
    {
        $this->_eventManager->dispatch('bms_order_preparation_instock_grid', ['grid' => $this]);

        $this->addExportType('*/*/exportinstockcsv', __('CSV'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        if($this->_config->create()->isBatchEnable()){
            $param["wh_id"] = $this->_preparationRegistry->getCurrentWarehouseId();
            $param["type"] = \BoostMyShop\OrderPreparation\Model\Batch\Type\MultipleProduct::CODE;

            $url = $this->getUrl("orderpreparation/preparation/massCreatebatch",$param);
            $this->getMassactionBlock()->addItem(
                'masscreatebatch',
                [
                    'label' => __('Generate batch'),
                    'url' => $url,
                ]
            );
        }
    }

    public function addExportType($url, $label)
    {
        $this->_exportTypes[] = new \Magento\Framework\DataObject(
            ['url' => $this->getUrl($url), 'label' => $label]
        );
        return $this;
    }

    public function getColumn($columnId)
    {
        try{
            return $this->getColumnSet()->getChildBlock($columnId);
        }
        catch(\Exception $e)
        {
            return null;
        }
    }

    public function getInStockCollection($cartbinsize)
    {
        $this->_defaultLimit = $cartbinsize;
        $this->_prepareCollection();
        return $this->getCollection();
    }
}
