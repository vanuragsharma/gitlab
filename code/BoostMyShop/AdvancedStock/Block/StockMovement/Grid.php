<?php

namespace BoostMyShop\AdvancedStock\Block\StockMovement;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_userCollectionFactory;
    protected $_stockMovementCollectionFactory;
    protected $_warehouseCollectionFactory;
    protected $_categories;
    protected $_coreRegistry;
    protected $_config;
    protected $_policyInterface;
    protected $_authSession;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\StockMovement\CollectionFactory $stockMovementCollectionFactory
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory
     * @param \BoostMyShop\AdvancedStock\Model\StockMovement\Category $categories
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     * @internal param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @internal param \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\StockMovement\CollectionFactory $stockMovementCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\StockMovement\Category $categories,
        \Magento\Framework\Authorization\PolicyInterface $policyInterface,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $coreRegistry,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_userCollectionFactory = $userCollectionFactory;
        $this->_stockMovementCollectionFactory = $stockMovementCollectionFactory;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_categories = $categories;
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;
        $this->_policyInterface = $policyInterface;
        $this->_authSession = $authSession;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('stockMovementGrid');
        $this->setDefaultSort('sm_created_at');
        $this->setDefaultDir('DESC');
        $this->setTitle(__('Stock Movements'));
        //$this->setUseAjax(true);  <== not compatible with mass actions
        $this->setSaveParametersInSession(true);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_stockMovementCollectionFactory->create();
        $this->_addAdditionnalFilterForCollection($collection);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _addAdditionnalFilterForCollection(&$collection)
    {
        return $collection;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('sm_created_at', ['header' => __('Date'), 'index' => 'sm_created_at', 'type' => 'datetime']);
        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'renderer' => 'BoostMyShop\AdvancedStock\Block\StockMovement\Renderer\Sku']);
        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);
        $this->addColumn('sm_from_warehouse_id', ['header' => __('From'), 'align' => 'center', 'index' => 'sm_from_warehouse_id', 'type' => 'options', 'options' => $this->getWarehouseOptions()]);
        $this->addColumn('sm_to_warehouse_id', ['header' => __('To'), 'align' => 'center', 'index' => 'sm_to_warehouse_id', 'type' => 'options', 'options' => $this->getWarehouseOptions()]);
        $this->addColumn('sm_qty', ['header' => __('Qty'), 'align' => 'center', 'type' => 'number', 'index' => 'sm_qty']);
        $this->addColumn('sm_direction', ['header' => __(' '), 'index' => ' ', 'filter' => false, 'align' => 'center', 'sortable' => 'false', 'renderer' => 'BoostMyShop\AdvancedStock\Block\StockMovement\Renderer\Direction']);
        //$this->addColumn('sm_new_qty', ['header' => __('Stock'), 'type' => 'number', 'index' => 'sm_new_qty', 'align' => 'center']);
        $this->addColumn('sm_category', ['header' => __('Category'), 'index' => 'sm_category', 'type' => 'options', 'options' => $this->_categories->toOptionArray()]);
        $this->addColumn('sm_comments', ['header' => __('Comments'), 'index' => 'sm_comments']);
        $this->addColumn('sm_user_id', ['header' => __('User'),'index' => 'sm_user_id','type' => 'options','options' => $this->_getManagerAsOptions()]);
        if ($this->_config->displayAdvancedLog()) {
            $this->addColumn('sm_ui', ['header' => __('UID'), 'index' => 'sm_ui']);
            $this->addColumn('sm_logs', ['header' => __('Logs'), 'index' => 'sm_id', 'filter' => false, 'align' => 'center', 'sortable' => 'false', 'renderer' => 'BoostMyShop\AdvancedStock\Block\StockMovement\Renderer\Logs']);
        }

        if ($this->canDeleteStockMovement())
            $this->addColumn('delete_sm', ['header' => __('Delete'), 'index' => 'sm_id', 'filter' => false, 'align' => 'center', 'sortable' => 'false', 'renderer' => 'BoostMyShop\AdvancedStock\Block\StockMovement\Renderer\Delete']);

        $this->_eventManager->dispatch('bms_advanced_stock_stock_movement_grid_prepare_columns', ['grid' => $this]);
        
        return parent::_prepareColumns();
    }



    public function getRowUrl($item){
        //empty to not get link to #
    }

    public function getWarehouseOptions()
    {
        $options = [];
        foreach($this->_warehouseCollectionFactory->create() as $item)
        {
            $options[$item->getId()] = $item->getw_name();
        }
        return $options;
    }

    /**
     * @return array $managers
     */
    protected function _getManagerAsOptions(){

        $managers = [];

        foreach($this->_userCollectionFactory->create() as $user){

            $managers[$user->getId()] = $user->getusername();

        }

        return $managers;

    }

    protected function canDeleteStockMovement()
    {
        $result = false;

        if ($this->_authSession)
        {
            $user = $this->_authSession->getUser();
            if ($user)
                $result = $this->_policyInterface->isAllowed($user->getRole()->getId(), 'BoostMyShop_AdvancedStock::stock_movement_delete');
        }

        return $result;
    }

    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('sm_id');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getMassActionUrl('massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

    }

    protected function _prepareMassactionColumn()
    {
        $columnId = 'massaction';
        $massactionColumn = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Grid\Column::class)
            ->setData(
                [
                    'index' => $this->getMassactionIdField(),
                    'filter_index' => $this->getMassactionIdFilter(),
                    'type' => 'massaction',
                    'name' => $this->getMassactionBlock()->getFormFieldName(),
                    'is_system' => true,
                    'use_index' => true,
                    'header_css_class' => 'col-select',
                    'column_css_class' => 'col-select',
                ]
            );

        if ($this->getNoFilterMassactionColumn()) {
            $massactionColumn->setData('filter', false);
        }

        $massactionColumn->setSelected($this->getMassactionBlock()->getSelected())->setGrid($this)->setId($columnId);

        $this->getColumnSet()->insert(
            $massactionColumn,
            count($this->getColumnSet()->getColumns()) + 1,
            false,
            $columnId
        );
        return $this;
    }

    protected function getMassActionUrl($action)
    {
        return $this->getUrl('*/*/'.$action);
    }

}
