<?php
namespace BoostMyShop\OrderPreparation\Block\Batch\Edit\Tab;

class Main  extends Base
{
    protected $_template = 'BoostMyShop_OrderPreparation::Batch/tabs/main.phtml';

    protected $_batch;
    protected $_registry;
    protected $_warehouses;
    protected $_carrierHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\OrderPreparation\Model\Config\Source\Warehouses $warehouses,
        \BoostMyShop\OrderPreparation\Helper\Carrier $carrierHelper,
        array $data = []
    )
    {
        $this->_registry = $registry;
        $this->_warehouses = $warehouses;
        $this->_carrierHelper = $carrierHelper;
        parent::__construct($context, $registry, $data);
    }

    public function getWarehouse()
    {
        $warehouses = $this->_warehouses->toOptionArray();
        return (isset($warehouses[$this->getBatch()->getbob_warehouse_id()]) ? $warehouses[$this->getBatch()->getbob_warehouse_id()] : "Default");
    }

    public function getShippingMethods($selectedCode)
    {
        $methods = [];

        foreach($this->_carrierHelper->getAllCarriers() as $carrier)
        {
            $methods[$carrier->getId()] = $carrier->getName();
        }

        return isset($methods[$selectedCode])?$methods[$selectedCode]:null;
    }
}