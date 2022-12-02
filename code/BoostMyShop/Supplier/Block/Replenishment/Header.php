<?php
namespace BoostMyShop\Supplier\Block\Replenishment;

class Header extends \Magento\Backend\Block\Template
{
    protected $_template = 'Replenishment/Header.phtml';
    protected $_config;
    protected $_supplierCollectionFactory;
    protected $_warehouseFactory;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\CollectionFactory $supplierCollectionFactory,
        \BoostMyShop\Supplier\Model\Registry $config,
        \BoostMyShop\Supplier\Model\Source\WarehouseFactory $warehouse,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_warehouseFactory = $warehouse;
        $this->_supplierCollectionFactory = $supplierCollectionFactory;
        $this->_config = $config;
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/CreateOrder');
    }

    public function getStatisticsPopupUrl()
    {
        return $this->getUrl('*/*/popup');
    }

    public function getSuppliers()
    {
        return $this->_supplierCollectionFactory->create()->addActiveFilter()->setOrder('sup_name', 'ASC');
    }

    public function getChangeWarehouseUrl()
    {
        return $this->getUrl('*/*/ChangeWarehouse', array('warehouse_id' => 'param_warehouse_id'));
    }

    public function isMultipleWarehouse()
    {
        return count($this->getWarehouses()) > 1;
    }

    public function getWarehouses()
    {
        $options[] = array('value' => '', 'label' => 'All Warehouses');
        $options = array_merge($options,$this->_warehouseFactory->create()->toOptionArray());
        return $options;
    }

    public function getCurrentWarehouseId()
    {
        return $this->_config->getCurrentWarehouseId();
    }

}