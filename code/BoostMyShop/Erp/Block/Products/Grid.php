<?php

namespace BoostMyShop\Erp\Block\Products;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_productCollectionFactory;
    protected $_config;
    protected $_orderPreparationConfig;
    protected $_bmsHelper = null;
    protected $_eavConfig;
    protected $_websiteCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\OrderPreparation\Model\Config $orderPreparationConfig,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Eav\Model\Config $eavConfig,
        \BoostMyShop\Erp\Helper\Boostmyshop $bmsHelper,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;
        $this->_orderPreparationConfig = $orderPreparationConfig;
        $this->_bmsHelper = $bmsHelper;
        $this->_eavConfig = $eavConfig;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('erpProductsGrid');
        $this->setTitle(__('ERP -Products'));
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('status');
        $collection->addAttributeToSelect('thumbnail');
        $collection->addAttributeToSelect('cost');
        $collection->addAttributeToSelect('supply_discontinued');

        if ($this->_config->getBarcodeAttribute())
            $collection->addAttributeToSelect($this->_config->getBarcodeAttribute());

        if ($this->_config->getManufacturerAttribute())
            $collection->addAttributeToSelect($this->_config->getManufacturerAttribute());

        if ($this->_orderPreparationConfig->getMpnAttribute())
            $collection->addAttributeToSelect($this->_orderPreparationConfig->getMpnAttribute());

        $this->_eventManager->dispatch('bms_erp_product_grid_prepare_collection', ['collection' => $collection]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function sortColumnsByOrder()
    {
        foreach ($this->getColumnsOrder() as $columnId => $after) {
            $this->getLayout()->reorderChild(
                $this->getColumnSet()->getNameInLayout(),
                $this->getColumn($columnId)->getNameInLayout(),
                $this->getColumn($after)?$this->getColumn($after)->getNameInLayout():null
            );
        }

        $columns = $this->getColumnSet()->getChildNames();
        $this->_lastColumnId = array_pop($columns);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn('image', ['header' => __('Image'),'filter' => false, 'sortable' => false, 'type' => 'renderer', 'renderer' => '\BoostMyShop\Erp\Block\Products\Renderer\Image', 'is_system' => true]);
        $this->addColumn('id', ['header' => __('ID'), 'index' => 'entity_id', 'type' => 'number']);
        $this->addColumn('type_id', ['header' => __('Type'), 'index' => 'type_id']);
        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'renderer' => '\BoostMyShop\Erp\Block\Products\Renderer\Sku']);

        if ($this->_orderPreparationConfig->getMpnAttribute())
            $this->addColumn($this->_orderPreparationConfig->getMpnAttribute(), ['header' => __('MPN'), 'index' => $this->_orderPreparationConfig->getMpnAttribute()]);

        if ($this->_config->getBarcodeAttribute())
            $this->addColumn($this->_config->getBarcodeAttribute(), ['header' => __('Barcode'), 'index' => $this->_config->getBarcodeAttribute()]);

        if ($this->_config->getManufacturerAttribute())
            $this->addColumn($this->_config->getManufacturerAttribute(), ['header' => __('Manufacturer'), 'index' => $this->_config->getManufacturerAttribute(), 'type' => 'options', 'options' => $this->getManufacturerOptions()]);


        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);
        $this->addColumn('status', ['header' => __('Status'), 'index' => 'status', 'type' => 'options', 'options' => [1 => 'Enabled', 2 => 'Disabled']]);
        $this->addColumn('supply_discontinued', ['header' => __('Discontinued'), 'index' => 'supply_discontinued', 'type' => 'options', 'options' => [0 => __('No'), 1 => __('Yes')]]);
        $this->addColumn('cost', ['header' => __('Cost'), 'index' => 'cost', 'type' => 'number']);

        if($this->_bmsHelper->advancedStockModuleIsInstalled())
            $this->addColumn('stock_details', ['header' => __('Stock details'), 'filter_index' => 'entity_id', 'sortable' => false, 'index' => 'entity_id', 'align' => 'left', 'renderer' => 'BoostMyShop\Erp\Block\Products\Renderer\StockDetails', 'filter' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Filter\StockDetails']);
        else
            $this->addColumn('stock_details', ['header' => __('Stock details'), 'filter' => false, 'sortable' => false, 'index' => 'entity_id', 'align' => 'left', 'renderer' => 'BoostMyShop\Erp\Block\Products\Renderer\StockDetails']);

        if ($this->hasMultipleWebsites())
            $this->addColumn('websites', ['header' => __('Websites'), 'index' => 'entity_id', 'sortable' => false, 'align' => 'left', 'renderer' => 'BoostMyShop\Erp\Block\Products\Renderer\Website', 'filter' => 'BoostMyShop\Erp\Block\Products\Filter\Website']);

        //$this->addColumn('sales_history', ['header' => __('Sales History'), 'index' => 'entity_id', 'sortable' => false, 'filter' => false, 'align' => 'center', 'renderer' => 'BoostMyShop\Erp\Block\Renderer\History']);
        $this->addColumn('suppliers', ['header' => __('Suppliers'), 'index' => 'entity_id', 'sortable' => false, 'align' => 'left', 'renderer' => 'BoostMyShop\Supplier\Block\Replenishment\Renderer\Suppliers', 'filter' => 'BoostMyShop\Supplier\Block\Replenishment\Filter\Suppliers']);

        //$this->addColumn('expected_po', ['header' => __('Expected PO'), 'index' => 'entity_id', 'filter' => false, 'sortable' => false, 'align' => 'left', 'renderer' => 'BoostMyShop\Erp\Block\Products\Renderer\ExpectedPo']);

        $this->_eventManager->dispatch('bms_erp_product_grid', ['grid' => $this]);

        $this->addExportType($this->getUrl('*/*/exportCsv', ['_current' => true]),__('CSV'));

        $this->updatedGridColumn();

        return parent::_prepareColumns();
    }

    protected function updatedGridColumn()
    {
        if(!$this->_bmsHelper->isAllowedResource("BoostMyShop_Erp::products")){        
            $this->removeColumn('sku');
            $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'frame_callback' => array( $this,'styleSku')]);
        }

        if(!$this->_bmsHelper->isAllowedResource("BoostMyShop_Supplier::suppliers")){
            $this->removeColumn('suppliers');
        }

        $this->_eventManager->dispatch('bms_erp_product_grid_column_update', ['grid' => $this]);
    }

    public function styleSku( $sku,$row,$column,$isExport )
    {
        $t = explode('_', $sku);
        if (isset($t[0]))
            unset($t[0]);
        return implode('_', $t);
    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajax');
    }

    public function getRowUrl($row)
    {
        if(!$this->_bmsHelper->isAllowedResource("BoostMyShop_Erp::products"))
            return "javascript:void(0)";

        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    public function getManufacturerOptions()
    {
        $options = array();

        $attribute = $this->_eavConfig->getAttribute('catalog_product', $this->_config->getManufacturerAttribute());
        $attributeOptions = $attribute->getSource()->getAllOptions();

        foreach($attributeOptions as $item)
        {
            $options[$item['value']] = $item['label'];
        }

        return $options;
    }

    public function hasMultipleWebsites()
    {
        $collection = $this->_websiteCollectionFactory->create();
        return (count($collection) > 1);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');

        $this->getMassactionBlock()->addItem(
            'discontinued_true',
            [
                'label' => __('Set product as discontinued'),
                'url' => $this->getMassActionUrl('massDiscontinued'),
                'confirm' => __('Are you sure?')
            ]
        );

        $this->getMassactionBlock()->addItem(
            'discontinued_false',
            [
                'label' => __('Set product as not discontinued'),
                'url' => $this->getMassActionUrl('massNotDiscontinued'),
                'confirm' => __('Are you sure?')
            ]
        );

        //todo : add a restriction to delete permission
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete products'),
                'url' => $this->getMassActionUrl('massDelete'),
                'confirm' => __('Do you really want to delete these products ? This operation can not be rollbacked')
            ]
        );

        $this->_eventManager->dispatch('bms_erp_products_grid_preparemassaction', ['grid' => $this]);
        
        return $this;
    }

    protected function getMassActionUrl($action)
    {
        return $this->getUrl('*/*/'.$action);
    }
}
