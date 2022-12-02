<?php
namespace BoostMyShop\OrderPreparation\Block\Batch\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Orders extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_inProgressFactory = null;
    protected $_preparationRegistry;
    protected $_batch;
    protected $_registry;
    protected $_orderStatuses;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressFactory,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatuses,
        array $data = []
    )
    {
        $this->_inProgressFactory = $inProgressFactory;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_registry = $registry;
        $this->_orderStatuses = $orderStatuses;

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

        $this->setId('batch_tab_in_progress');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }

    public function setBatch($batch)
    {
        $this->_batch = $batch;
        return $this;
    }

    public function getBatch()
    {
        if(!$this->_batch)
            $this->_batch = $this->_registry->registry('current_batch');

        return $this->_batch;
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_inProgressFactory->create();
        $collection->addOrderDetails();

        $warehouseId = $this->getBatch()->getbob_warehouse_id()?$this->getBatch()->getbob_warehouse_id():$this->_preparationRegistry->getCurrentWarehouseId();
        $collection->addWarehouseFilter($warehouseId);
        $collection->addbatchFilter($this->getBatch()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', ['header' => __('#'), 'index' => 'increment_id']);
        $this->addColumn('created_at', ['header' => __('Date'), 'index' => 'created_at', 'filter_index' => 'created_at', 'type' => 'datetime', 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime', 'format' => \IntlDateFormatter::FULL]);
        $this->addColumn('status', ['header' => __('Status'), 'index' => 'status', 'filter_index' => 'status', 'type' => 'options', 'options' => $this->getMagentoStatusesOptions()]);
        $this->addColumn('shipping_name', ['header' => __('Customer'), 'index' => 'shipping_name']);
        $this->addColumn('shipping_information', ['header' => __('Shipping method'), 'index' => 'shipping_information']);
        $this->addColumn('products', ['header' => __('Products'), 'index' => 'ip_order_id', 'renderer' => '\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\InProgressProducts', 'filter' => '\BoostMyShop\OrderPreparation\Block\Preparation\Filter\InProgressProducts']);
        $this->addColumn('ip_shipping_label_pregenerated_status',
            [
                'header' => __('Shipping label'),
                'index' => 'ip_shipping_label_pregenerated_status',
                'type' => "options",
                'options' => [\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPING_LABEL_PREGENERATED_PENDING => "Pending", \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPING_LABEL_PREGENERATED_OK => "Ok", \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPING_LABEL_PREGENERATED_ERROR => "Error"],
                'frame_callback' => array($this,'styleLabel')
            ]
        );
        $this->addColumn('action', ['header' => __('Action'), 'index' => 'index_id', 'align' => 'center', 'filter' => false, 'renderer' => \BoostMyShop\OrderPreparation\Block\Batch\Edit\Tab\Renderer\Actions::class]);

        return parent::_prepareColumns();
    }

    public function styleLabel($value, $row, $column, $isExport)
    {
        if($row->getip_shipping_label_pregenerated_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPING_LABEL_PREGENERATED_OK)
        {
            $downloadUrl = $this->getUrl('orderpreparation/packing/download', ['document' => "shipping_label", 'order_id' => $row->getId()]);
            $status = '<a href="'.$downloadUrl.'">'.$row->getip_shipping_label_pregenerated_tracking().'</a>';
            return $status;
        }

        return $value;
    }

    protected function getMagentoStatusesOptions()
    {
        $options = [];
        foreach($this->_orderStatuses->toOptionArray() as $item)
            $options[$item['value']] = $item['label'];
        return $options;
    }

    public function getGridUrl()
    {
        return $this->getUrl('orderpreparation/batch/inprogressAjaxGrid', ['_current' => true, 'grid' => 'selected']);
    }
}