<?php

namespace BoostMyShop\OrderPreparation\Block\Manifest;


class Create extends \Magento\Framework\View\Element\Template
{
    protected $_warehouses;
    protected $_carrierHelper;
    protected $_preparationRegistry;
    protected $_formKey;
    protected $_templateCollectionFactory;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
                                \BoostMyShop\OrderPreparation\Model\Config\Source\Warehouses $warehouses,
                                \BoostMyShop\OrderPreparation\Helper\Carrier $carrierHelper,
                                \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
                                \Magento\Framework\Data\Form\FormKey $formKey,
                                \BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate\CollectionFactory $templateCollectionFactory
    )
    {
        parent::__construct($context);
        $this->_warehouses = $warehouses;
        $this->_carrierHelper = $carrierHelper;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_formKey = $formKey;
        $this->_templateCollectionFactory = $templateCollectionFactory;
    }

    public function getWarehouse()
    {
        $warehouses = $this->_warehouses->toOptionArray();
        return $warehouses;
    }

    public function getCarriers()
    {
        $methods = [];
        $allowedCarriers = $this->getAllowedCarriers();
        foreach($this->_carrierHelper->getCarriers() as $carrier)
        {
            if(in_array($carrier->getId(), $allowedCarriers))
                $methods[$carrier->getId()] = $carrier->getName();
        }

        asort($methods);
        return $methods;
    }

    protected function getAllowedCarriers()
    {
        $data = [];
        foreach($this->_templateCollectionFactory->create() as $carrierTemplate)
        {
            foreach (unserialize($carrierTemplate->getct_shipping_methods()) as $method) {
                $carrier = explode("_", $method);
                if(isset($carrier[0]) && !in_array($carrier[0], $data))
                    $data[] = $carrier[0];
            }
        }

        return $data;
    }

    public function getCurrentWarehouse()
    {
        $result = $this->_preparationRegistry->getCurrentWarehouseId();
        return $result;
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('orderpreparation/manifest/save');
    }

    public function getSearchShipmentsUrl()
    {
        return $this->getUrl('orderpreparation/manifest/shipments');
    }

    public function getManifestExportUrl()
    {
        return $this->getUrl('orderpreparation/manifest/export');
    }

    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }
}
