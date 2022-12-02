<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

/**
 * Class History
 * @package BoostMyShop\Supplier\Block\Order\Edit\Tab
 */
class History extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
    protected $_orderHistoryFactory = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\History\CollectionFactory $orderHistoryFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_orderHistoryFactory = $orderHistoryFactory;
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('orderHistoryGrid');
        $this->setDefaultSort('poh_date');
        $this->setDefaultDir('desc');
        $this->setTitle(__('History'));
        $this->setUseAjax(true);
    }

    protected function getOrder()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }

    protected function _prepareCollection()
    {
        $collection = $this->_orderHistoryFactory->create();
        $collection->addPoFilter($this->getOrder()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('poh_date', ['sortable' => true, 'align' => 'center', 'header' => __('Date'), 'index' => 'poh_date', 'type' => 'datetime', 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime', 'format' => \IntlDateFormatter::FULL]);
        $this->addColumn('poh_username', ['sortable' => true, 'align' => 'center', 'header' => __('User'), 'index' => 'poh_username', 'type' => 'text']);
        $this->addColumn('poh_description', ['sortable' => true, 'align' => 'left', 'header' => __('Details'), 'index' => 'poh_description', 'type' => 'text']);

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('supplier/order_history/grid', ['po_id' => $this->getOrder()->getId()]);
    }
}
