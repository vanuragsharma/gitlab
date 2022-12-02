<?php

namespace BoostMyShop\AdvancedStock\Block\MassStockEditor;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_warehouseCollectionFactory;
    protected $_coreRegistry;
    protected $_massStockEditorCollectionFactory;
    protected $_config;
    protected $_eavConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\MassStockEditor\CollectionFactory $massStockEditorCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_massStockEditorCollectionFactory = $massStockEditorCollectionFactory;

        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;

        $this->_eavConfig = $eavConfig;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('massStockEditorGrid');
        $this->setDefaultSort('sm_id');
        $this->setDefaultDir('DESC');
        $this->setTitle(__('Mass Stock Editor'));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_massStockEditorCollectionFactory->create();

        if ($this->_config->getManufacturerAttribute())
            $collection->addAttributeToSelect($this->_config->getManufacturerAttribute());

        $this->_eventManager->dispatch('bms_advancedstock_massStockeditor_grid_preparecollection', ['collection' => $collection]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', ['header' => __('ID'), 'index' => 'entity_id', 'type' => 'number']);
        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'renderer' => '\BoostMyShop\AdvancedStock\Block\MassStockEditor\Renderer\Sku']);

        if ($this->_config->getBarcodeAttribute())
            $this->addColumn($this->_config->getBarcodeAttribute(), ['header' => __('Barcode'), 'index' => 'entity_id', 'align' => 'center','filter_index' => 'entity_id','renderer' => '\BoostMyShop\AdvancedStock\Block\MassStockEditor\Renderer\AdditionalBarcodes','filter' => '\BoostMyShop\AdvancedStock\Block\MassStockEditor\Filter\AdditionalBarcodes']);

        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);

        if ($this->_config->getManufacturerAttribute())
            $this->addColumn($this->_config->getManufacturerAttribute(), ['header' => __('Manufacturer'), 'index' => $this->_config->getManufacturerAttribute(), 'type' => 'options', 'options' => $this->getManufacturerOptions()]);

        $this->addColumn('status', ['header' => __('Status'), 'index' => 'status', 'type' => 'options', 'align' => 'center', 'options' => $this->getStatusesOptions()]);
        $this->addColumn('wi_warehouse_id', ['header' => __('Warehouse'), 'index' => 'wi_warehouse_id', 'align' => 'center', 'type' => 'options', 'options' => $this->getWarehouseOptions()]);
        $this->addColumn('wi_physical_quantity', ['header' => __('Qty in warehouse'), 'type' => 'number', 'align' => 'center', 'index' => 'wi_physical_quantity', 'renderer' => '\BoostMyShop\AdvancedStock\Block\MassStockEditor\Renderer\PhysicialQuantity']);
        $this->addColumn('wi_quantity_to_ship', ['header' => __('Qty to ship'), 'type' => 'number', 'align' => 'center', 'index' => 'wi_quantity_to_ship']);
        $this->addColumn('wi_available_quantity', ['header' => __('Available Qty'), 'type' => 'number', 'align' => 'center', 'index' => 'wi_available_quantity']);
        $this->addColumn('wi_shelf_location', ['header' => __('Shelf location'), 'align' => 'center', 'index' => 'wi_shelf_location', 'renderer' => '\BoostMyShop\AdvancedStock\Block\MassStockEditor\Renderer\ShelfLocation']);
        $this->_eventManager->dispatch('bms_advancedstock_massstockeditor_grid_preparecolumns', ['grid' => $this]);

        $url = $this->getUrl('*/*/exportProductsCsv', ['_current' => true]);
        //var_dump($url); die();

        $this->addExportType('*/*/exportProductsCsv', __('CSV'));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }

    public function getRowUrl($item){
        //empty to not get link to #
    }

    public function getWarehouseOptions()
    {
        $options = [];
        $options[''] = ' ';
        foreach($this->_warehouseCollectionFactory->create()->addActiveFilter() as $item)
        {
            $options[$item->getId()] = $item->getw_name();
        }
        return $options;
    }

    public function getStatusesOptions()
    {
        $options = [];
        $options[1] = __('Enabled');
        $options[2] = __('Disabled');
        return $options;
    }

    public function addExportType($url, $label)
    {
        $this->_exportTypes[] = new \Magento\Framework\DataObject(
            ['url' => $this->getUrl($url), 'label' => $label]
        );
        return $this;
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

}


