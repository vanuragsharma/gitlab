<?php
namespace BoostMyShop\OrderPreparation\Block\Manifest;

class Shipments extends \Magento\Framework\View\Element\Template
{
    protected $_manifestHelper;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
                                \BoostMyShop\OrderPreparation\Helper\Manifest $manifestHelper
    )
    {
        parent::__construct($context);
        $this->_manifestHelper = $manifestHelper;
    }

    public function getShipments()
    {
        $carrier = $this->getRequest()->getParam('carrier');
        $warehouseId = $this->getRequest()->getParam('warehouseId');
        $fromDate = $this->getRequest()->getParam('from_date');
        $shipments = $this->_manifestHelper->listShipments($carrier, $warehouseId, $fromDate);
        return $shipments;
    }

    public function removePrefix($value)
    {
        $t = explode('_', $value);
        if (count($t) > 1)
            unset($t[0]);
        return implode('_', $t);
    }
}
