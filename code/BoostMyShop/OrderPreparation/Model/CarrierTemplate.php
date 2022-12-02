<?php

namespace BoostMyShop\OrderPreparation\Model;


class CarrierTemplate extends \Magento\Framework\Model\AbstractModel
{
    const kTypeOrderDetailsExport = 'order_details_export';
    const kTypeSimpleAddressLabel = 'simple_address_label';
    const kTypeShippo = 'shippo';
    const kTypeBoxtal = 'boxtal';
    const kTypeAmazon = 'amazon';
    const kTypeDelivengo = 'delivengo';
    const kTypeDpdStation3 = 'dpdstation3';
    const kTypeUpsOffline = 'upsoffline';
    const kTypeMondialrelay = 'mondial_relay';
    const kTypeLaPoste = 'laposte';
    const kTypeDpdCzech = 'dpdczech';

    protected $_eventManager;

    protected $_rendererOrderDetailExport;
    protected $_rendererSimpleAddressLabel;
    protected $_csvTrackingExtractHandler;
    protected $_inProgressFactory;
    protected $_rendererShippo;
    protected $_rendererBoxtal;
    protected $_rendererAmazon;
    protected $_rendererDelivengo;
    protected $_rendererDpdStation3;
    protected $_rendererUpsOffline;
    protected $_rendererMondialRelay;
    protected $_configFactory;
    protected $_rendererLaPoste;
    protected $_rendererDpdCzech;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\OrderDetailsExport $rendererOrderDetailExport,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\SimpleAddressLabel $rendererSimpleAddressLabel,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\Shippo $rendererShippo,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\Boxtal $rendererBoxtal,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\Amazon $rendererAmazon,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\Delivengo $rendererDelivengo,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\DpdStation3 $rendererDpdStation3,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\UpsOffline $rendererUpsOffline,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\LaPoste $rendererLaPoste,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\DpdCzech $rendererDpdCzech,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer\MondialRelay $rendererMondialRelay,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Extract\CsvTrackingExtractHandler $csvTrackingExtractHandler,
        \BoostMyShop\OrderPreparation\Model\InProgressFactory $inProgressFactory,
        \BoostMyShop\OrderPreparation\Model\ConfigFactory $configFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    )
    {
        $this->_rendererOrderDetailExport = $rendererOrderDetailExport;
        $this->_rendererSimpleAddressLabel = $rendererSimpleAddressLabel;
        $this->_rendererShippo = $rendererShippo;
        $this->_rendererBoxtal = $rendererBoxtal;
        $this->_rendererAmazon = $rendererAmazon;
        $this->_rendererDelivengo = $rendererDelivengo;
        $this->_rendererDpdStation3 = $rendererDpdStation3;
        $this->_rendererUpsOffline = $rendererUpsOffline;
        $this->_rendererLaPoste = $rendererLaPoste;
        $this->_rendererDpdCzech = $rendererDpdCzech;
        $this->_rendererMondialRelay = $rendererMondialRelay;
        $this->_csvTrackingExtractHandler = $csvTrackingExtractHandler;
        $this->_inProgressFactory = $inProgressFactory;
        $this->_configFactory = $configFactory;
        $this->_eventManager = $eventManager;

        parent::__construct($context, $registry, null, null, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate');
    }

    /**
     * return the shipping label file
     */
    public function getShippingLabelFile($ordersInProgress, $forceNoPackedOrders = false)
    {
        $renderer = $this->getRenderer();
        if (!$renderer)
            throw new \Exception('No renderer available for type shipping label template "'.$this->getct_type().'"');
        else {
            if (!$forceNoPackedOrders)
                $ordersInProgress = $this->filterOrdersInProgress($ordersInProgress, true);
            return $renderer->getShippingLabelFile($ordersInProgress, $this);
        }

    }

    /**
     * returns an array with for each label : pdf object, tracking number(s), shipping cost
     */
    public function getShippingLabelData($ordersInProgress, $forceNoPackedOrders = false)
    {
        $renderer = $this->getRenderer();
        if (!$renderer)
            throw new \Exception('No renderer available for type shipping label template "'.$this->getct_type().'"');
        else {
            if (!$forceNoPackedOrders)
                $ordersInProgress = $this->filterOrdersInProgress($ordersInProgress, true);
            return $renderer->getShippingLabelData($ordersInProgress, $this);
        }

    }

    public function generateReturnLabel($ordersInProgress, $customerAddress)
    {
        $renderer = $this->getRenderer();
        if (!$renderer)
            throw new \Exception('No renderer available for type shipping label template "'.$this->getct_type().'"');
        else
            return $renderer->generateReturnLabel($ordersInProgress, $customerAddress);
    }

    public function getRenderer()
    {
        $renderer = null;
        switch($this->getct_type())
        {
            case self::kTypeOrderDetailsExport:
                $renderer = $this->_rendererOrderDetailExport;
                break;
            case self::kTypeSimpleAddressLabel:
                $renderer = $this->_rendererSimpleAddressLabel;
                break;
            case self::kTypeShippo:
                $renderer = $this->_rendererShippo;
                break;
            case self::kTypeBoxtal:
                $renderer = $this->_rendererBoxtal;
                break;
            case self::kTypeAmazon:
                $renderer = $this->_rendererAmazon;
                break;
            case self::kTypeDelivengo:
                $renderer = $this->_rendererDelivengo;
                break;
            case self::kTypeUpsOffline:
                $renderer = $this->_rendererUpsOffline;
                break;
            case self::kTypeDpdStation3:
                $renderer = $this->_rendererDpdStation3;
                break;
            case self::kTypeMondialrelay:
                $renderer = $this->_rendererMondialRelay;
                break;
            case self::kTypeLaPoste:
                $renderer = $this->_rendererLaPoste;
                break;
            case self::kTypeDpdCzech:
                $renderer = $this->_rendererDpdCzech;
                break;
        }

        //raise event so other modules can inject renderers
        $obj = new \Magento\Framework\DataObject();
        $obj->setrenderer(false);
        $this->_eventManager->dispatch('bms_orderpreparation_carrier_template_get_renderer', ['type' => $this->getct_type(), 'renderer' => $obj]);
        if ($obj->getRenderer())
            $renderer = $obj->getRenderer();

        if (!$renderer)
            return null;
        else {
            return $renderer;
        }

    }

    /**
     * Method executed once the shipment is done for order in progress
     * Todo : implement dedicated classes for this, design is not good here :(
     *
     * @param $orderInProgress
     */
    public function afterShipment($orderInProgress)
    {
        if ($orderInProgress->getSkipCarrierTemplate()) //mostly used by dropship module that uses order preparation funciton to create the shipment
            return;

        switch($this->getct_type())
        {
            case self::kTypeShippo:
            case self::kTypeBoxtal:
            case self::kTypeAmazon:
            case self::kTypeMondialrelay:
            case self::kTypeDelivengo:
            case self::kTypeLaPoste:
            case self::kTypeDpdCzech:
                //force label generation
                $this->getShippingLabelFile([$orderInProgress]);
                break;
            case self::kTypeUpsOffline:
                if($orderInProgress->getip_custom_data())
                    return;
                //force label generation
                $this->getShippingLabelFile([$orderInProgress]);
                break;
            default:
                //nothing by default but raise event
                $this->_eventManager->dispatch('bms_orderpreparation_carrier_template_after_shipment', ['order_in_progress' => $orderInProgress, 'carrier_template' => $this]);
                break;
        }

    }

    public function filterOrdersInProgress($ordersInProgress, $hydrate = false)
    {
        $orders = [];

        foreach($ordersInProgress as $orderInProgress)
        {
            if (!$orderInProgress->getShipment())
                continue;
            if ($hydrate)
                $orderInProgress->hydrateWithOrderInformation();
            if (!$this->shippingMethodSupported($orderInProgress->getshipping_method()))
                continue;
            $orders[] = $orderInProgress;
        }

        return $orders;
    }

    public function shippingMethodSupported($code)
    {
        $supportedMethods = unserialize($this->getct_shipping_methods());
        foreach($supportedMethods as $method)
        {
            $pattern = '/'.$method.'/';
            if (preg_match($pattern, $code))
                return true;
        }
        return false;
    }

    public function importTracking($fileContent)
    {
        $stats = ['success' => 0, 'error' => 0, 'details' => []];
        $datas = $this->_csvTrackingExtractHandler->extract($fileContent, $this);
        foreach($datas as $data)
        {
            try
            {
                if ((!$data['shipment'] && !$data['order']) || !$data['tracking'])
                    throw new \Exception(__('Unable to import record : Shipment (%1) order (%2) reference (%3) : %4', $data['shipment'], $data['order'], $data['tracking'], $data['msg']));

                if ($data['shipment'])
                {
                    $inProgress = $this->_inProgressFactory->create()->loadByShipmentReference($data['shipment']);
                }
                else
                {
                    $inProgress = $this->_inProgressFactory->create()->loadByOrderReference($data['order']);
                    if ($inProgress && !$inProgress->getip_shipment_id() && $this->getct_import_create_shipment())
                    {
                        $inProgress->pack(true, $this->_configFactory->create()->getCreateInvoice());
                    }
                }

                if (!$inProgress->getId())
                    throw new \Exception('Can not find order in progress');

                $inProgress->addTracking($data['tracking']);
                $stats['success']++;
            }
            catch(\Exception $ex)
            {
                $stats['details'][] = $ex->getMessage();
                $stats['error']++;
            }
        }

        return $stats;
    }

    public function getCustomValue($key)
    {
        $customValues = [];
        if ($this->getct_custom())
            $customValues = unserialize($this->getct_custom());

        return (isset($customValues[$key]) ? $customValues[$key] : '');
    }

}