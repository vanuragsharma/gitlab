<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation;

use Magento\Backend\Block\Widget\Grid\Column;

class Tab extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
    protected $_orderStatuses;
    protected $_preparationRegistry = null;
    protected $_config = null;
    protected $_ordersFactory = null;
    protected $_inProgressCollectionFactory = null;
    protected $_carrierHelper;

    protected static $_tabId = 1;

    protected static $_selectedOrderIds = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

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
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatuses,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\Order\CollectionFactory $ordersFactory,
        \BoostMyShop\OrderPreparation\Model\ConfigFactory $config,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressCollectionFactory,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
        \BoostMyShop\OrderPreparation\Helper\Carrier $carrierHelper,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_orderStatuses = $orderStatuses;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_ordersFactory = $ordersFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_config = $config;
        $this->_inProgressCollectionFactory = $inProgressCollectionFactory;
        $this->_carrierHelper = $carrierHelper;

        parent::__construct($context, $backendHelper, $data);

        $this->setMessageBlockVisibility(false);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {

        $collection = $this->_ordersFactory->create();
        $collection->addAdditionalFields();

        $collection->addFieldToFilter('main_table.status', ['in' => $this->getAllowedOrderStatuses()]);

        //exclude orders being prepared (we use static as it applies for the 4 tabs
        if (self::$_selectedOrderIds === null)
        {
            if ($this->_config->create()->getManyOrdersMode())
            {
                $query = $this->_inProgressCollectionFactory->create();
                $query->addFieldToFilter('ip_warehouse_id', $this->getWarehouseId());
                $query->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(new \Zend_Db_Expr('distinct ip_order_id'));
                self::$_selectedOrderIds = new \Zend_Db_Expr((string)$query->getSelect());
            }
            else
            {
                $ids = $this->_inProgressCollectionFactory->create()->getOrderIds($this->_preparationRegistry->getCurrentWarehouseId());
                $ids[] = -1;
                self::$_selectedOrderIds = $ids;
            }
        }
        $collection->addFieldToFilter('main_table.entity_id', array('nin' => self::$_selectedOrderIds));

        $this->_eventManager->dispatch('bms_order_preparation_tab_prepare_collection', ['collection' => $collection, 'tab' => $this]);

        //add filter on warehouse
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();
        $this->addWarehouseFilter($collection, $warehouseId);

        $this->addAdditionnalFilters($collection);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    public function getWarehouseId()
    {
        return $this->_preparationRegistry->getCurrentWarehouseId();
    }

    public function getAllowedOrderStatuses()
    {
        //to override by children
    }

    public function addAdditionnalFilters($collection)
    {
        //to override by children
    }

    public function addWarehouseFilter(&$collection, $warehouseId)
    {
        //not implement in order preparaiton, as order / warehouse logic doesnt exist
        //function rewritten by advanced stock module

        return $this;
    }

        /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', ['header' => __('#'), 'index' => 'increment_id', 'filter_index' => 'main_table.increment_id']);
        $this->addColumn('created_at', ['header' => __('Date'), 'index' => 'created_at', 'filter_index' => 'main_table.created_at', 'type' => 'datetime', 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime', 'format' => \IntlDateFormatter::FULL]);
        $this->addColumn('status', ['header' => __('Status'), 'index' => 'status',  'filter_index' => 'main_table.status', 'type' => 'options', 'options' => $this->getMagentoStatusesOptions()]);
        $this->addColumn('store_id', ['header' => __('Store'), 'index' => 'store_id', 'filter_index' => 'main_table.store_id', 'is_system' => true, 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Store', 'filter'=>'Magento\Backend\Block\Widget\Grid\Column\Filter\Store', 'store_all' => true]);
        $this->addColumn('shipping_name', ['header' => __('Customer'), 'index' => 'shipping_name', 'filter_index' => 'main_table.shipping_name', 'renderer' => '\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\CustomerName']);
        $this->addColumn('shipping_information', ['header' => __('Shipping method'), 'index' => 'shipping_information', 'filter_index' => 'so.shipping_method']);
        $this->addColumn('weight', ['header' => __('Weight'), 'index' => 'weight', 'type' => 'number']);
        $this->addColumn('total_item_count', ['header' => __('Items count'), 'index' => 'total_item_count', 'type' => 'number']);
        $this->addColumn('products', ['header' => __('Products'), 'index' => 'entity_id', 'sortable' => false, 'filter_index' => 'main_table.entity_id', 'renderer' => '\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\Products', 'filter' => '\BoostMyShop\OrderPreparation\Block\Preparation\Filter\Products']);
        $this->addColumn('action', ['header' => __('Action'), 'index' => 'index_id', 'filter_index' => 'main_table.entity_id', 'align' => 'center', 'filter' => false, 'is_system' => true, 'renderer' => '\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\Actions']);
        $this->_eventManager->dispatch('bms_order_preparation_order_grid', ['grid' => $this]);
        return parent::_prepareColumns();
    }


    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');

        if(!$this->_config->create()->isBatchEnable()){
            $this->getMassactionBlock()->addItem(
                'prepare',
                [
                    'label' => __('Prepare'),
                    'url' => $this->getUrl('*/*/massPrepare', ['_current' => true]),
                ]
            );
        }

        $this->getMassactionBlock()->addItem(
            'change_shipping_method',
            [
                'label' => __('Change shipping method'),
                'url' => $this->getUrl('*/*/massChangeShippingMethod', ['_current' => true]),
                'additional' => [
                    'shipping_method' => [
                        'name' => 'shipping_method',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Shipping method'),
                        'values' => $this->getShippingMethodsMassAction(),
                    ],
                ]
            ]
        );
        $this->_eventManager->dispatch('bms_order_preparation_preparation_tab_massaction', ['obj' => $this]);

    }

    protected function getMagentoStatusesOptions()
    {
        $options = [];
        foreach($this->_orderStatuses->toOptionArray() as $item)
            $options[$item['value']] = $item['label'];
        return $options;
    }

    protected function getShippingMethodsMassAction()
    {
        $methods = [];

        foreach($this->_carrierHelper->getCarriers() as $carrier)
        {
            foreach($this->_carrierHelper->getMethods($carrier) as $methodCode => $method)
            {
                $methods[$methodCode] = $carrier->getName().' - '.$method;
            }
        }

        asort($methods);

        return $methods;
    }


}
