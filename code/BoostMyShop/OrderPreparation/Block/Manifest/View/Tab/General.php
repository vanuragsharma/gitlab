<?php

namespace BoostMyShop\OrderPreparation\Block\Manifest\View\Tab;


class General extends Base
{
    protected $_template = 'BoostMyShop_OrderPreparation::Manifest/View/Tab/General.phtml';
    protected $_manifest;
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
        if(isset($warehouses[$this->getManifest()->getbom_warehouse_id()]))
            $warehouse = $warehouses[$this->getManifest()->getbom_warehouse_id()];

        return $warehouse;
    }

    public function getShippingMethod()
    {
        $methods = [];

        foreach($this->_carrierHelper->getAllCarriers() as $carrier)
        {
            $methods[$carrier->getId()] = $carrier->getName();
        }

        return isset($methods[$this->getManifest()->getbom_carrier()])?$methods[$this->getManifest()->getbom_carrier()]:null;
    }

    public function getShipmentCount()
    {
        return $this->getManifest()->getbom_shipment_count();
    }

    public function getCreatedAt()
    {
        return $this->getManifest()->getbom_date();
    }

    public function getStatus()
    {
        return $this->getManifest()->getbom_edi_status();
    }

}