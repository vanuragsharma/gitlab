<?php

namespace Yalla\Vendors\Block\Adminhtml\Orderitems;

use Yalla\Vendors\Model\VendorProductsFactory;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
    
    /**
     * @var \Leza\Vendors\Model\VendorProductsFactory
     */
    protected $_vendorProductsFactory;
    
    /**
     * @var  \Magento\Framework\Registry
     */
    protected $registry;

    protected $_objectManager = null;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param VendorProductsFactory $vendorProductsFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        VendorProductsFactory $vendorProductsFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_vendorProductsFactory = $vendorProductsFactory;
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->resource = $resource;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('orderItemsGrid');
        $this->setDefaultSort('item_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * add Column Filter To Collection
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'products_list') {
            //$column->getFilter()->getValue();
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
    	$connection  = $this->resource->getConnection();
	    $vendorProductTable   = $connection->getTableName('yalla_vendor_products');
  	    $vendorsTable   = $connection->getTableName('yalla_vendors');
	    
        //$orderFactory = $this->_objectManager->create('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory')->create();
        $orderTable = $connection->getTableName('sales_order');
        $orderItemRepository = $this->_objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory')->create();

        $orderItemRepository->filterByParent(null);
        $orderItemRepository->getSelect()->joinLeft(array('sales_flat_order'=> $orderTable), 'main_table.order_id = sales_flat_order.entity_id',array('sales_flat_order.increment_id', 'sales_flat_order.status','sales_flat_order.customer_group_id'));
        
        $orderItemRepository->getSelect()->joinLeft(array('vendor_products'=> $vendorProductTable), 'main_table.product_id = vendor_products.product_id',array('vendor_products.vendor_id'));
        
        $orderItemRepository->getSelect()->joinLeft(array('vendors'=> $vendorsTable), 'vendor_products.vendor_id = vendors.vendor_id',array('vendors.vendor_name', 'vendors.vendor_email'));
        
        $orderItemRepository->getSelect()->where('sales_flat_order.status != "pending" && sales_flat_order.status != "canceled" && sales_flat_order.status != "closed" && sales_flat_order.status != "complete" && vendors.vendor_email !=""');
        
        $this->setCollection($orderItemRepository);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'item_id',
            [
                'header' => __('Order Item ID'),
                'type' => 'number',
                'index' => 'item_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        
        $this->addColumn(
            'product_id',
            [
                'header' => __('Product ID'),
                'type' => 'number',
                'index' => 'product_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'type' => 'text',
                'index' => 'sku',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'type' => 'text',
                'index' => 'name',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        
        $this->addColumn(
            'increment_id',
            [
                'header' => __('Order Number'),
                'type' => 'text',
                'index' => 'increment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        
        $this->addColumn(
            'vendor_name',
            [
                'header' => __('Vendor'),
                'type' => 'text',
                'index' => 'vendor_name',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        
        $this->addColumn(
            'email_sent',
            [
                'header' => __('Email Sent'),
                'type' => 'text',
                'index' => 'email_sent',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/orderitemsgrid', ['_current' => true]);
    }

    /**
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    protected function _getSelectedProducts()
    {
        $vendor_id = $this->getRequest()->getParam('id');
        $vendorProductsModel = $this->_vendorProductsFactory->create();
        $products = $vendorProductsModel->getCollection()
                        ->addFieldToFilter('vendor_id', $vendor_id);
        
        $selectedProducts = [];
        if(count($products)){
            foreach($products as $product){
                $selectedProducts[] = $product['product_id'];
            }
        }
        
        return $selectedProducts;
    }

    /**
     * Retrieve selected products
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        $selected = [];
        return $selected;
    }
    
    protected function _prepareMassaction()
	{
		$this->setMassactionIdField('item_id');
		$this->getMassactionBlock()->setFormFieldName('items_id[]');

		$this->getMassactionBlock()->addItem(
		    'export',
		    [
		        'label' => __('Email to Vendor'),
		        'url' => $this->getUrl('vendors/vendors/orderemail')
		    ]
		);

		return $this;
	}

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return true;
    }
}

