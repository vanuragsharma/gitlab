<?php
namespace BoostMyShop\OrderPreparation\Block\Manifest;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $_manifestFactory;
    protected $_carrierHelper;
    protected $_warehouses;
    protected $_templateCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\OrderPreparation\Model\ManifestFactory $manifestFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \BoostMyShop\OrderPreparation\Helper\Carrier $carrierHelper,
        \BoostMyShop\OrderPreparation\Model\Config\Source\Warehouses $warehouses,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate\CollectionFactory $templateCollectionFactory,
        array $data = []
    ) {
        $this->_manifestFactory = $manifestFactory;
        $this->moduleManager = $moduleManager;
        $this->_carrierHelper = $carrierHelper;
        $this->_warehouses = $warehouses;
        $this->_templateCollectionFactory = $templateCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('orderpreparationmanifest');
        $this->setDefaultSort('bom_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_manifestFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn('bom_id', ['header' => __('Id'), 'index' => 'bom_id']);
        $this->addColumn('bom_date', ['header' => __('Date'), 'index' => 'bom_date', 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime', 'type' => 'datetime']);
        $this->addColumn('bom_warehouse_id', ['header' => __('Warehouse'), 'index' => 'bom_warehouse_id', 'type' => 'options', 'options' => $this->getWarehouseOptions()]);
        $this->addColumn('bom_carrier', ['header' => __('Carrier'), 'index' => 'bom_carrier', 'type' => 'options', 'options' => $this->getShippingMethods()]);
        $this->addColumn('bom_shipment_count', ['header' => __('Shipment count'), 'index' => 'bom_shipment_count']);
        $this->addColumn('action', ['header' => __('Actions'), 'index' => 'false', 'align' => 'center', 'filter' => false, 'renderer' => \BoostMyShop\OrderPreparation\Block\Manifest\Renderer\ManifestActions::class]);
        $this->addColumn('view', ['header' => __('View'), 'index' => 'false', 'align' => 'center', 'filter' => false, 'renderer' => \BoostMyShop\OrderPreparation\Block\Manifest\Renderer\ManifestView::class]);
        return parent::_prepareColumns();
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('orderpreparation/manifest/grid', ['_current' => true]);
    }

    /**
     * @param \BoostMyShop\OrderPreparation\Model\batch|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {

        return "javascript:void(0)";

    }

    public function getShippingMethods()
    {
        $methods = [];
        $allowedCarriers = $this->getAllowedCarriers();
        foreach($this->_carrierHelper->getCarriers() as $carrier)
        {
            if(in_array($carrier->getId(), $allowedCarriers))
                $methods[$carrier->getId()] = $carrier->getName();
        }

        asort($methods);
        return $methods;
    }

    protected function getAllowedCarriers()
    {
        $data = [];
        foreach($this->_templateCollectionFactory->create() as $carrierTemplate)
        {
            foreach (unserialize($carrierTemplate->getct_shipping_methods()) as $method) {
                $carrier = explode("_", $method);
                if(isset($carrier[0]) && !in_array($carrier[0], $data))
                    $data[] = $carrier[0];
            }
        }

        return $data;
    }

    public function getWarehouseOptions()
    {
        return $this->_warehouses->toOptionArray();
    }


}