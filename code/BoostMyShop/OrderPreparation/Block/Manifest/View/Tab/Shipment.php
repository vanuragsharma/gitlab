<?php

namespace BoostMyShop\OrderPreparation\Block\Manifest\View\Tab;


class Shipment extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_manifest = null;
    protected $_registry;
    protected $_orderShipmentCollectionFactory;
    protected $_config;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        array $data = []
    )
    {
        $this->_registry = $registry;
        $this->_orderShipmentCollectionFactory = $orderShipmentCollectionFactory;
        $this->_config = $config;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('manifest_shipment_tab');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    public function getManifest()
    {
        if (!$this->_manifest)
            $this->_manifest = $this->_registry->registry('current_manifest');

        return $this->_manifest;
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $warehouseId = $this->getManifest()->getbom_warehouse_id();
        $collection = $this->_orderShipmentCollectionFactory->create()
            ->addFieldToFilter('manifest_id', $this->getManifest()->getId());
        if($this->_config->isAdvancedstockModuleInstall())
            $collection->addFieldToFilter('warehouse_id', $warehouseId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', ['header' => __('#'), 'index' => 'increment_id']);
        $this->addColumn('created_at', ['header' => __('Date'), 'index' => 'created_at', 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime', 'format' => \IntlDateFormatter::FULL]);
        $this->addColumn('total_weight', ['header' => __('Total weight'), 'index' => 'total_weight']);

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('orderpreparation/manifest/shipmentajaxgrid', ['_current' => true, 'grid' => 'selected']);
    }
}