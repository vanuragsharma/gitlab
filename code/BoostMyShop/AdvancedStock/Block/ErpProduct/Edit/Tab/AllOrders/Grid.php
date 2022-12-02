<?php

namespace BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Tab\AllOrders;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_pendingOrdersCollectionFactory;
    protected $_coreRegistry;
    protected $_config;
    protected $_warehouseCollectionFactory;
    protected $_orderStatuses;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Product\PendingOrders\CollectionFactory $pendingOrdersCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatuses,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_pendingOrdersCollectionFactory = $pendingOrdersCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_orderStatuses = $orderStatuses;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('allorders');
        $this->setDefaultSort('order_date');
        $this->setDefaultDir('DESC');
        $this->setTitle(__('All orders'));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('order_date', ['header' => __('Date'), 'index' => 'order_date', 'filter_index' => 'order_date', 'type' => 'date', 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime', 'format' => \IntlDateFormatter::FULL]);
        $this->addColumn('order_increment_id', ['header' => __('Order #'), 'index' => 'order_increment_id', 'renderer' => 'BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\PendingOrders\Renderer\Order']);
        $this->addColumn('order_status', ['header' => __('Status'), 'index' => 'order_status', 'filter_index' => 'order_status', 'type' => 'options', 'options' => $this->getMagentoStatusesOptions()]);
        $this->addColumn('store_id', ['header' => __('Store'), 'index' => 'store_id', 'filter_index' => 'main_table.store_id', 'is_system' => true, 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Store', 'filter' =>'Magento\Backend\Block\Widget\Grid\Column\Filter\Store', 'store_all' => true]);
        $this->addColumn('order_customer_name', ['header' => __('Customer'), 'index' => 'order_customer_name']);
        $this->addColumn('qty_ordered', ['header' => __('Qty ordered'), 'index' => 'qty_ordered', 'type' => 'number']);
        $this->addColumn('qty_to_ship', ['header' => __('Qty to ship'), 'index' => 'esfoi_qty_to_ship', 'type' => 'number']);
        $this->addColumn('esfoi_qty_reserved', ['header' => __('Qty reserved'), 'index' => 'esfoi_qty_reserved', 'type' => 'number']);
        $this->addColumn('esfoi_warehouse_id', ['header' => __('Shipping warehouse'), 'index' => 'esfoi_warehouse_id', 'type' => 'options', 'options' => $this->getWarehouseOptions()]);

        $this->_eventManager->dispatch('bms_advancedstock_all_orders_grid', ['grid' => $this]);

        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = $this->_pendingOrdersCollectionFactory->create();
        $collection->addProductFilter($this->getProduct());
        $collection->addOrderDetailsWithoutQtyRestriction();
        $collection->addOrderExtendedDetails();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('advancedstock/erpproduct_allorders/grid', ['product_id' => $this->getProduct()->getId()]);
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getWarehouseOptions()
    {
        $options = [];
        $options[''] = __('--- All ---');
        foreach($this->_warehouseCollectionFactory->create()->addActiveFilter() as $item)
        {
            $options[$item->getId()] = $item->getw_name();
        }
        return $options;
    }

    protected function getMagentoStatusesOptions()
    {
        $options = [];
        foreach($this->_orderStatuses->toOptionArray() as $item)
            $options[$item['value']] = $item['label'];

        return $options;
    }
}