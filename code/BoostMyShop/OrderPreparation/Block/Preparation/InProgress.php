<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation;

use Magento\Backend\Block\Widget\Grid\Column;

class InProgress extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
    protected $_orderStatuses ;
    protected $_inProgressFactory = null;
    protected $_preparationRegistry;
    protected $_config;
    protected $_carrierHelper;
    protected $_carrierTemplateCollectionFactory;

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
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatuses,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressFactory,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
        \BoostMyShop\OrderPreparation\Helper\Carrier $carrierHelper,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate\CollectionFactory $carrierTemplateCollectionFactory,
        array $data = []
    ) {
        $this->_orderStatuses = $orderStatuses;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_inProgressFactory = $inProgressFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_config = $config;
        $this->_carrierHelper = $carrierHelper;
        $this->_carrierTemplateCollectionFactory = $carrierTemplateCollectionFactory;

        parent::__construct($context, $backendHelper, $data);

        $this->setMessageBlockVisibility(false);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('tab_in_progress');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_inProgressFactory->create();
        $collection->addOrderDetails();

        $userId = $this->_preparationRegistry->getCurrentOperatorId();
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();

        $collection->addUserFilter($userId);
        $collection->addWarehouseFilter($warehouseId);
        //$collection->addStoreFilter($storeId);
        $this->_eventManager->dispatch('bms_order_preparation_tab_inprogress_collection', ['collection' => $collection, 'tab' => $this]);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn('increment_id', ['header' => __('#'), 'index' => 'increment_id', 'filter_index' => 'sales_order_grid.increment_id']);
        $this->addColumn('created_at', ['header' => __('Date'), 'index' => 'created_at', 'filter_index' => 'sales_order_grid.created_at', 'type' => 'datetime', 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime', 'format' => \IntlDateFormatter::FULL]);
        $this->addColumn('status', ['header' => __('Status'), 'index' => 'status', 'filter_index' => 'sales_order_grid.status', 'type' => 'options', 'options' => $this->getMagentoStatusesOptions()]);
        $this->addColumn('store_id', ['header' => __('Store'), 'index' => 'store_id', 'is_system' => true, 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Store', 'filter_index' => 'sales_order_grid.store_id', 'filter' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Store', 'store_all' => true]);
        $this->addColumn('shipping_name', ['header' => __('Customer'), 'index' => 'shipping_name', 'renderer' => '\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\CustomerName']);
        $this->addColumn('shipping_information', ['header' => __('Shipping method'), 'index' => 'shipping_information']);
        $this->addColumn('products', ['header' => __('Products'), 'index' => 'ip_order_id', 'renderer' => '\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\InProgressProducts', 'filter' => '\BoostMyShop\OrderPreparation\Block\Preparation\Filter\InProgressProducts']);
        $this->addColumn('total_weight', ['header' => __('Weight'), 'index' => 'ip_total_weight', 'type' => 'number']);

        if ($this->_config->getVolumeAttribute())
            $this->addColumn('total_volume', ['header' => __('Volume'), 'index' => 'ip_total_volume', 'type' => 'number']);

        $this->addColumn('ip_status', ['header' => __('Progress'), 'index' => 'ip_status', 'type' => 'options', 'options' => $this->getStatusOptions()]);
        $this->addColumn('action', ['header' => __('Action'), 'index' => 'index_id', 'align' => 'center', 'filter' => false, 'is_system' => true, 'renderer' => '\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\InProgressActions']);

        $this->_eventManager->dispatch('bms_order_preparation_inprogress_grid', ['grid' => $this]);

        $this->addExportType('*/*/exportinprogresscsv', __('CSV'));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/inprogressAjaxGrid', ['_current' => true, 'grid' => 'selected']);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.ip_id');
        $this->getMassactionBlock()->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');

        $this->getMassactionBlock()->addItem(
            'mass_remove',
            [
                'label' => __('Remove'),
                'url' => $this->getUrl('*/*/massRemove', ['_current' => true]),
            ]
        );

        $this->getMassactionBlock()->addItem(
            'print_packing_pdf',
            [
                'label' => __('Print pick list PDF'),
                'url' => $this->getUrl('*/*/massPrintPackingPdf', ['_current' => true]),
            ]
        );

        $this->getMassactionBlock()->addItem(
            'change_shipping_method',
            [
                'label' => __('Change shipping method'),
                'url' => $this->getUrl('*/*/massChangeShippingMethodForInProgress', ['_current' => true]),
                'additional' => [
                    'shipping_method' => [
                        'name' => 'shipping_method',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Shipping method'),
                        'values' => $this->getShippingMethods(),
                    ],
                ]
            ]
        );

        $this->getMassactionBlock()->addItem(
            'shipping_label_download',
            [
                'label' => __('Shipping label'),
                'url' => $this->getUrl('*/*/massShippingLabelDownload', ['_current' => true]),
                'additional' => [
                    'template_id' => [
                        'name' => 'template_id',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Template'),
                        'values' => $this->getCarrierTemplates(),
                    ],
                ]
            ]
        );

        $this->_eventManager->dispatch('bms_order_preparation_inprogress_prepare_massaction', ['grid' => $this]);
    }

    protected function getMagentoStatusesOptions()
    {
        $options = [];
        foreach($this->_orderStatuses->toOptionArray() as $item)
            $options[$item['value']] = $item['label'];
        return $options;
    }

    protected function getStatusOptions()
    {
        $options = [];

        $options[\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_NEW] = __(\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_NEW);
        $options[\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PICKED] = __(\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PICKED);
        $options[\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PACKED] = __(\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PACKED);
        $options[\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED] = __(\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED);

        return $options;
    }

    protected function getShippingMethods()
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

    protected function getCarrierTemplates()
    {
        $templates = [];

        foreach($this->_carrierTemplateCollectionFactory->create() as $carrier)
        {
            $templates[$carrier->getId()] = $carrier->getct_name();
        }

        asort($templates);

        return $templates;
    }

    public function addExportType($url, $label)
    {
        $this->_exportTypes[] = new \Magento\Framework\DataObject(
            ['url' => $this->getUrl($url), 'label' => $label]
        );
        return $this;
    }

    protected function _prepareFilterButtons()
    {
        if ($this->_config->getPickingPerBins()){
            $url = $this->getUrl('orderpreparation/preparation/addXOrders', ['cartbinsize' => $this->_config->getCartBinSize()]);
            $this->setChild(
                'picking_per_bins',
                $this->getLayout()->createBlock(
                    \Magento\Backend\Block\Widget\Button::class
                )->setData(
                    [
                        'label' => __('Add %1 orders', $this->_config->getCartBinSize()),
                        'onclick' => 'window.location.href = "'.$url.'"',
                        'class' => 'action-secondaryy'
                    ]
                )
            );
        }
        parent::_prepareFilterButtons();
    }

    public function getSearchButtonHtml()
    {
        if ($this->_config->getPickingPerBins()){
            return $this->getChildHtml('search_button') . $this->getChildHtml('picking_per_bins');
        }

        return $this->getChildHtml('search_button');
    }

    public function getColumn($columnId)
    {
        try{
            return $this->getColumnSet()->getChildBlock($columnId);
        }
        catch(\Exception $e)
        {
            return null;
        }
    }
}
