<?php

namespace BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class OrdersToShip extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
    protected $_pendingOrderCollectionFactory = null;
    protected $_warehouseCollectionFactory = null;
    protected $_jsonEncoder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Product\PendingOrders\CollectionFactory $pendingOrderCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);

        $this->_pendingOrderCollectionFactory = $pendingOrderCollectionFactory;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
    }


    protected function _construct()
    {
        parent::_construct();
        $this->setId('ordersGrid');
        $this->setDefaultSort('item_id');
        $this->setDefaultDir('desc');
        $this->setTitle(__('Products'));
        $this->setUseAjax(true);
    }

    protected function getWarehouse()
    {
        return $this->_coreRegistry->registry('current_warehouse');
    }


    protected function _prepareCollection()
    {
        $collection = $this->_pendingOrderCollectionFactory->create()->addOrderDetails()->addExtendedDetails()->addSimpleProductsFilter();
        $collection->addWarehouseFilter($this->getWarehouse()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {
        $this->addColumn('order_date', ['header' => __('Date'), 'index' => 'order_date']);
        $this->addColumn('order_increment_id', ['header' => __('Order #'), 'index' => 'order_increment_id', 'renderer' => 'BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab\Renderer\Order']);
        $this->addColumn('status', ['header' => __('Status'), 'index' => 'order_status']);
        $this->addColumn('qty_to_ship', ['header' => __('Qty to ship'), 'type' => 'number', 'align' => 'center', 'index' => 'qty_to_ship', 'filter_index' => 'esfoi_qty_to_ship']);
        $this->addColumn('esfoi_qty_reserved', ['header' => __('Qty reserved'), 'type' => 'number', 'align' => 'center', 'index' => 'esfoi_qty_reserved', 'filter_index' => 'esfoi_qty_reserved']);
        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);

        return parent::_prepareColumns();
    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/ordersGrid', ['w_id' => $this->getWarehouse()->getId()]);
    }


    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('item_id');

        $this->getMassactionBlock()->addItem(
            'change_warehouse',
            [
                'label' => __('Change shipping warehouse'),
                'url' => $this->getUrl('*/*/massChangeShippingWarehouse', ['w_id' => $this->getWarehouse()->getId()]),
                'additional' => [
                    'warehouse' => [
                        'name' => 'target_warehouse',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Warehouse'),
                        'values' => $this->getWarehouses(),
                    ],
                ]
            ]
        );

    }

    protected function getWarehouses()
    {
        $warehouses = [];

        foreach($this->_warehouseCollectionFactory->create() as $warehouse)
        {
            $warehouses[$warehouse->getId()] = $warehouse->getw_name();
        }

        asort($warehouses);

        return $warehouses;
    }

}
