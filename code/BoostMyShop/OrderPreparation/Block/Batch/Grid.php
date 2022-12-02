<?php
namespace BoostMyShop\OrderPreparation\Block\Batch;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $_batchFactory;
    protected $_preparationRegistry;
    protected $_carrierHelper;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\OrderPreparation\Model\BatchFactory $batchFactory,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
        \Magento\Framework\Module\Manager $moduleManager,
        \BoostMyShop\OrderPreparation\Helper\Carrier $carrierHelper,
        array $data = []
    ) {
        $this->_batchFactory = $batchFactory;
        $this->moduleManager = $moduleManager;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_carrierHelper = $carrierHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('orderpreparationbatch');
        $this->setDefaultSort('bob_id');
        $this->setDefaultDir('desc');
        $this->setDefaultLimit(100);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();
        $collection = $this->_batchFactory->create()->getCollection();
        $collection->addWarehouseFilter($warehouseId);

        $this->setCollection($collection);
        $this->addAdditionalFilters($collection);
        parent::_prepareCollection();

        return $this;
    }

    public function addAdditionalFilters($collection)
    {
        //to override
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn('bob_id', ['header' => __('#'), 'index' => 'bob_id']);
        $this->addColumn('bob_created_at', ['header' => __('Date'), 'index' => 'bob_created_at', 'filter_index' => 'bob_created_at', 'type' => 'datetime', 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime', 'format' => \IntlDateFormatter::FULL]);
        $this->addColumn('bob_type', ['header' => __('Type'), 'index' => 'bob_type']);
        $this->addColumn('bob_carrier', ['header' => __('Carrier'), 'index' => 'bob_carrier', 'type' => 'options', 'options' => $this->getShippingMethods()]);
        $this->addColumn('bob_label', ['header' => __('Label'), 'index' => 'bob_label']);
        $this->addColumn('bob_order_count', ['header' => __('Order count'), 'index' => 'bob_order_count', 'type' => 'number']);
        $this->addColumn('bob_status', ['header' => __('Status'), 'index' => 'bob_status']);
        $this->addColumn('bob_progress', ['header' => __('Progress'), 'index' => 'bob_progress', 'type' => 'number']);

        $this->addColumn('action', ['header' => __('Action'), 'index' => 'index_id', 'align' => 'center', 'filter' => false, 'renderer' => \BoostMyShop\OrderPreparation\Block\Batch\Renderer\BatchActions::class]);

        $this->_eventManager->dispatch('bms_order_preparation_batch_main_grid', ['grid' => $this]);

        return parent::_prepareColumns();
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('orderpreparation/batch/index', ['_current' => true]);
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
        $methods[''] = __('--- All ---');
        foreach($this->_carrierHelper->getAllCarriers() as $carrier)
        {
            $methods[$carrier->getId()] = $carrier->getName();
        }

        asort($methods);
        return $methods;
    }

}