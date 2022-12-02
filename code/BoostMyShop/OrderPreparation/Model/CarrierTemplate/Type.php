<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    protected $moduleManager;
    protected $_eventManager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Event\ManagerInterface $eventManager
    )
    {
        $this->moduleManager = $moduleManager;
        $this->_eventManager = $eventManager;
    }

    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray()
    {
        $methods = [];

        $methods['order_details_export'] = __('Order details');
        $methods['simple_address_label'] = __('Address label');
        $methods['dpdstation3'] = __('DPD Station');

        if ($this->moduleManager->isEnabled('BoostMyShop_Shippo')) {
            $methods['shippo'] = __('Shippo Label');
        }

        if ($this->moduleManager->isEnabled('BoostMyShop_Boxtal')) {
            $methods['boxtal'] = __('Boxtal Label');
        }

        if ($this->moduleManager->isEnabled('BoostMyShop_AmazonLabel')) {
            $methods['amazon'] = __('Amazon Label');
        }

        if ($this->moduleManager->isEnabled('MondialRelay_Shipping')) {
            $methods['mondial_relay'] = __('Mondial Relay');
        }

        if ($this->moduleManager->isEnabled('BoostMyShop_Delivengo')) {
            $methods['delivengo'] = __('Delivengo Label');
        }

        if ($this->moduleManager->isEnabled('BoostMyShop_UpsLabel')) {
            $methods['upsoffline'] = __('UPS Label');
        }

        if ($this->moduleManager->isEnabled('BoostMyShop_LaPoste')) {
            $methods['laposte'] = __('La Poste Label');
        }

        if ($this->moduleManager->isEnabled('BoostMyShop_DpdCz')) {
            $methods['dpdczech'] = __('DPD Czech');
        }

        $methods['dpdstation3'] = __('DPD Station');

        //add event so other module can inject carrier template types
        $obj = new \Magento\Framework\DataObject();
        $obj->setmethods($methods);
        $this->_eventManager->dispatch('bms_orderpreparation_carrier_template_type_to_option_array', ['methods' => $obj]);
        $methods = $obj->getmethods();

        asort($methods);
        return $methods;
    }
}
