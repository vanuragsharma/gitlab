<?php

namespace BoostMyShop\OrderPreparation\Model;


class InProgress extends \Magento\Framework\Model\AbstractModel
{
    protected $_storeManager;
    protected $_userFactory;
    protected $_orderFactory;
    protected $_order;
    protected $_inProgressItemCollectionFactory;
    protected $_inProgressItemFactory;
    protected $_shipmentHelperFactory;
    protected $_invoiceHelperFactory;
    protected $_invoiceRepository;
    protected $_shipmentRepository;
    protected $_logger;
    protected $_configFactory;
    protected $_regionFactory;
    protected $_carrierTemplate = false;
    protected $_carrierTemplateHelper;
    protected $_eventManager;
    protected $_countryFactory;
    protected $_costMatrix;

    protected $_allItems = null;

    protected $_forceShipment = false;
    protected $_forceCarrierTemplate = false;

    const STATUS_NEW = 'new';
    const STATUS_PICKED = 'picked';
    const STATUS_PACKED = 'packed';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_SHIPPING_LABEL_PREGENERATED_PENDING = 0;
    const STATUS_SHIPPING_LABEL_PREGENERATED_OK = 1;
    const STATUS_SHIPPING_LABEL_PREGENERATED_ERROR = 2;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\User\Model\User $userFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Sales\Model\Order\InvoiceRepository $invoiceRepository,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\Item\CollectionFactory $inProgressItemCollectionFactory,
        \BoostMyShop\OrderPreparation\Helper\CarrierTemplate $carrierTemplateHelper,
        \BoostMyShop\OrderPreparation\Model\InProgress\ItemFactory $inProgressItemFactory,
        \BoostMyShop\OrderPreparation\Model\InProgress\ShipmentFactory $shipmentHelperFactory,
        \BoostMyShop\OrderPreparation\Model\InProgress\InvoiceFactory $invoiceHelperFactory,
        \BoostMyShop\OrderPreparation\Model\Config $_configFactory,
        \BoostMyShop\OrderPreparation\Helper\Logger $logger,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\CostMatrix $costMatrix,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_storeManager = $storeManager;
        $this->_userFactory = $userFactory;
        $this->_orderFactory = $orderFactory;
        $this->_inProgressItemCollectionFactory = $inProgressItemCollectionFactory;
        $this->_inProgressItemFactory = $inProgressItemFactory;
        $this->_shipmentHelperFactory = $shipmentHelperFactory;
        $this->_invoiceHelperFactory = $invoiceHelperFactory;
        $this->_invoiceRepository = $invoiceRepository;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_configFactory = $_configFactory;
        $this->_logger = $logger;
        $this->_regionFactory = $regionFactory;
        $this->_carrierTemplateHelper = $carrierTemplateHelper;
        $this->_eventManager = $eventManager;
        $this->_countryFactory = $countryFactory;
        $this->_costMatrix = $costMatrix;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress');
    }

    public function beforeDelete()
    {
        if ($this->getId())
            $this->_inProgressItemCollectionFactory->create()->deleteForParent($this->getId());
        return parent::beforeDelete();
    }

    public function getStore()
    {
        return $this->_storeManager->getStore($this->getip_store_id());
    }

    public function getOperatorName()
    {
        $user = $this->_userFactory->load($this->getip_user_id());
        return ucfirst($user->getfirstname()).' '.strtoupper(substr($user->getlastname(), 0, 1));
    }

    public function getOrder()
    {
        if (!$this->_order)
        {
            $this->_order = $this->_orderFactory->create()->load($this->getip_order_id());
        }
        return $this->_order;
    }

    public function getCustomerName()
    {
        $customerName = $this->getshipping_name();
        if (!$customerName)
        {
            $customerName = $this->getOrder()->getcustomer_firstname().' '.$this->getOrder()->getcustomer_lastname();
            if (strlen($customerName) < 2)
            {
                $shippingAddress = $this->getOrder()->getShippingAddress();
                if ($shippingAddress)
                    $customerName = $shippingAddress->getFirstname().' '.$shippingAddress->getLastname();
                else
                {
                    $billingAddress = $this->getOrder()->getBillingAddress();
                    $customerName = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
                }
            }
        }

        return $customerName;
    }

    public function setForceShipment($shipment)
    {
        $this->_forceShipment = $shipment;
    }

    public function getShipment()
    {
        //used to inject a dummy shipment object
        if ($this->_forceShipment)
            return $this->_forceShipment;

        if ($this->getip_shipment_id())
        {
            $shipment = $this->_shipmentRepository->get($this->getip_shipment_id());
            $shipment->setOrder($this->getOrder());
            return $shipment;
        }
    }

    public function getInvoice()
    {
        if ($this->getip_invoice_id())
        {
            return $this->_invoiceRepository->get($this->getip_invoice_id());
        }
    }

    public function addProduct($orderItemId, $qty)
    {
        $obj = $this->_inProgressItemFactory->create();
        $obj->setipi_order_id($this->getip_order_id());
        $obj->setipi_order_item_id($orderItemId);
        $obj->setipi_qty($qty);
        $obj->setipi_parent_id($this->getId());
        $obj->save();

        return $obj;
    }

    public function getAllItems()
    {
        if (!$this->_allItems)
        {
            if ($this->getId())
            {
                $this->_allItems = $this->_inProgressItemCollectionFactory
                    ->create()
                    ->addParentFilter($this->getId())
                    ->joinOrderItem();
            }
            else
                $this->_allItems = [];
        }
        return $this->_allItems;
    }

    public function setAllItems($items)
    {
        $this->_allItems = $items;
    }

    public function getLabel()
    {
        $shipName = "";
        if($this->getshipping_name())
            $shipName =  " (".$this->getshipping_name().")";
        return "#".$this->getOrder()->getincrementId().$shipName." - ".__($this->getip_status());
    }

    public function loadByShipmentReference($shipmentReference)
    {
        $id = $this->_getResource()->getIdFromShipmentReference($shipmentReference);
        return $this->load($id);
    }

    public function loadByOrderReference($orderReference)
    {
        $id = $this->_getResource()->getIdFromOrderReference($orderReference);
        return $this->load($id);
    }

    public function loadFromOrderIdAndContext($orderId, $warehouseId, $operatorId)
    {
        $id = $this->_getResource()->getIdFromOrderIdAndContext($orderId, $warehouseId, $operatorId);
        return $this->load($id);
    }

    /**
     *
     */
    public function pack($createShipment, $createInvoice, $quantities = null, $totalWeight = null, $parcelCount = null, $parcelHeight = null, $parcelWidth = null, $parcelLength = null)
    {
        $this->_eventManager->dispatch('bms_orderpreparation_inprogress_before_pack', ['in_progress' => $this]);

        if ($totalWeight)
            $this->setip_total_weight($totalWeight);
        if ($parcelCount)
            $this->setip_parcel_count($parcelCount);

        $this->setip_height($parcelHeight);
        $this->setip_width($parcelWidth);
        $this->setip_length($parcelLength);

        if ($createInvoice)
        {
            if ($this->getOrder()->canInvoice())
            {
                $this->_logger->log('Create invoice for order #'.$this->getOrder()->getIncrementId());

                $invoice = $this->_invoiceHelperFactory->create()->createInvoice($this, $quantities);
                $this->setip_invoice_id($invoice->getId())->save();
            }
        }
        else
            $this->_logger->log('DO NOT Create invoice for order #'.$this->getOrder()->getIncrementId());

        if ($createShipment)
        {
            $this->_logger->log('Create shipment for order #'.$this->getOrder()->getIncrementId());

            $shipment = $this->_shipmentHelperFactory->create()->createShipment($this, $quantities, $this->getip_user_id());
            if ($totalWeight)
                $shipment->settotal_weight($totalWeight);
            $packages = [];
            $packages[] = ['weight' => $totalWeight, 'height' => $parcelHeight, 'width' => $parcelWidth, 'length' => $parcelLength];
            $shipment->setPackages($packages);
            $shippmentCost = '';
            if($this->getCarrierTemplate())
                $shippmentCost = $this->_costMatrix->getCost($this->getCarrierTemplate(), $this);
            $shipment->setshipping_cost($shippmentCost);
            $shipment->setStockMovementCreated(1)->save();


            $this->setip_shipment_id($shipment->getId())->save();

        }
        else
            $this->_logger->log('DO NOT Create shipment for order #'.$this->getOrder()->getIncrementId());

        $this->setip_status(self::STATUS_PACKED)->save();

        $statusFromConfForCompelteOrder = $this->_configFactory->getOrderStateComplete();
        $statusFromConfForUncompleteOrder = $this->_configFactory->getOrderStateProcessing();
        $canChangeOrderStatusAfterPacking = $this->_configFactory->getChangeOrderStatusAfterPacking();
        if($canChangeOrderStatusAfterPacking)
        {
            $order = $this->_orderFactory->create()->load($this->getip_order_id());
            $orderState = $order->getState();
            if($statusFromConfForCompelteOrder){
                if($orderState == \Magento\Sales\Model\Order::STATE_COMPLETE)            
                    $order->setStatus($statusFromConfForCompelteOrder)->save();
            }
            if($statusFromConfForUncompleteOrder)
            {
                if($orderState == \Magento\Sales\Model\Order::STATE_PROCESSING)            
                    $order->setStatus($statusFromConfForUncompleteOrder)->save();
            }
        }

        //call at the end so tracking # is properly stored
        if ($createShipment && $this->getCarrierTemplate()){
            if($this->getip_shipping_label_pregenerated_status() != \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPING_LABEL_PREGENERATED_OK)
                $this->getCarrierTemplate()->afterShipment($this);
            else
            {
                $this->addTracking($this->getip_shipping_label_pregenerated_tracking(), false);
            }

        }

        $this->_eventManager->dispatch('bms_orderpreparation_inprogress_after_pack', ['in_progress' => $this]);

        return $this;
    }

    public function addTracking($trackingNumber, $notifyCustomer = true)
    {
        if (!$this->getShipment())
            throw new \Exception('No shipment available, unable to add tracking number');

        //try to update existing tracking number
        if ($trackingNumber)
        {
            foreach($this->getShipment()->getTracksCollection() as $tracking)
            {
                $tracking->setNumber($trackingNumber)->save();
                $this->_logger->log('Tracking # edited in shipment #'.$this->getShipment()->getId());
                return;
            }

            //no tracking to update, add it
            $this->_logger->log('Tracking # added to shipment #'.$this->getShipment()->getId());
            $this->_shipmentHelperFactory->create()->addTracking($this->getShipment(), $trackingNumber, '', '');
        }

        if ($notifyCustomer)
            $this->notifyCustomer();

        $this->_logger->log('Change status to shipped for shipment #'.$this->getShipment()->getId());
        $this->setip_status(self::STATUS_SHIPPED)->save();
        return $this;
    }

    public function CancelPregeneratedLabel()
    {
        if($this->getip_shipping_label_pregenerated_label_path())
        {
            try{
                if(is_file($this->getip_shipping_label_pregenerated_label_path()))
                    unlink($this->getip_shipping_label_pregenerated_label_path());
            } catch (\Exception $e)
            {
                throw new \Exception('Impossible to delete shipping label file : '. $e->getMessage());
            }

            $this
                ->setip_shipping_label_pregenerated_status(0)
                ->setip_shipping_label_pregenerated_tracking(null)
                ->setip_shipping_label_pregenerated_label_path(null)
                ->setip_dummy_shipment_increment_id(null)
                ->save();
        }
    }

    public function changeStatus($status)
    {
        $this->setip_status($status)->save();
    }

    public function getTrackingNumber()
    {
        if ($this->getShipment())
        {
            foreach($this->getShipment()->getTracksCollection() as $tracking)
                return $tracking->getNumber();
        }
    }

    public function notifyCustomer()
    {
        $this->_logger->log('Notify customer for shipment #'.$this->getShipment()->getId());
        $this->_shipmentHelperFactory->create()->notifyCustomer($this->getShipment());
    }


    /**
     * @param $orderInProgress
     */
    public function getDatasForExport()
    {
        $datas = [];

        foreach($this->getData() as $k => $v)
        {
            if ((!is_array($v)) && (!is_object($v)))
                $datas['preparation.'.$k] = $v;
        }
        $datas['preparation.ip_total_qty'] = $this->getProductsCount();
        $datas['preparation.ip_total_package'] = $this->getPackageCount();
        $datas['preparation.ip_product_skus'] = $this->getProductSkus();
        $datas['preparation.ip_product_names'] = $this->getProductNames();
        $datas['preparation.ip_parcel_count'] = $this->getip_parcel_count();

        $datas['preparation.ip_weights_gram'] = $this->kiloToGram(isset($datas['preparation.ip_weights']) ? $datas['preparation.ip_weights'] : 0);
        $datas['preparation.ip_total_weight_gram'] = $this->kiloToGram(isset($datas['preparation.ip_total_weight']) ? $datas['preparation.ip_total_weight'] : 0);

        foreach($this->getOrder()->getData() as $k => $v)
        {
            if ((!is_array($v)) && (!is_object($v)))
                $datas['order.'.$k] = $v;
        }

        if($this->getOrder()->getShippingAddress()){

            foreach($this->getOrder()->getShippingAddress()->getData() as $k => $v)
            {
                if ((!is_array($v)) && (!is_object($v)))
                    $datas['shippingaddress.'.$k] = $v;
            }

            $streetLines = $this->getOrder()->getShippingAddress()->getStreet();
            foreach($streetLines as $id => $line){
                $datas['shippingaddress.street_'.($id+1)] = $line;
            }

            if($datas['shippingaddress.region_id']){
                $region = $this->_regionFactory->create()->load($datas['shippingaddress.region_id']);
                if($region)
                    $datas['shippingaddress.region_name'] = $region->getName();
            }

            $country = $this->_countryFactory->create()->loadByCode($this->getOrder()->getShippingAddress()->getcountry_id());
            $datas['shippingaddress.country_name'] = $country->getName();

        }

        if ($this->getShipment()) {
            foreach ($this->getShipment()->getData() as $k => $v) {
                if ((!is_array($v)) && (!is_object($v)))
                    $datas['shipment.' . $k] = $v;
            }
            $datas['shipment.total_weight_gram'] = $this->kiloToGram(isset($datas['shipment.total_weight']) ? $datas['shipment.total_weight'] : 0);
        }

        if ($this->getInvoice()) {
            foreach ($this->getInvoice()->getData() as $k => $v) {
                if ((!is_array($v)) && (!is_object($v)))
                    $datas['invoice.' . $k] = $v;
            }
        }

        $datas['misc.ddmmyyyy'] = date('d/m/Y');
        $datas['misc.yyyymmdd'] = date('Y-m-d');

        //raise event to allow other modules to inject new fields
        $obj = new \Magento\Framework\DataObject();
        $obj->setDatas($datas);
        $this->_eventManager->dispatch('bms_orderpreparation_inprogress_get_data_for_export', ['datas' => $obj, 'in_progress' => $this]);
        $datas = $obj->getDatas();

        return $datas;
    }

    /**
     *
     */
    public function getEstimatedWeight()
    {
        $weight = 0;
        foreach($this->getAllItems() as $item)
        {
            $weight += $item->getWeight() * $item->getipi_qty();
        }
        return $weight;
    }

    public function getProductsCount()
    {
        $count = 0;
        if ($this->getId())
        {
            foreach($this->getAllItems() as $item)
            {
                $count += $item->getipi_qty();
            }
        }
        return $count;
    }

    public function getPackageCount()
    {

        if ($packageCountAttribute = $this->_configFactory->getPackageNumberAttribute())
        {
            $count = 0;
            foreach($this->getAllItems() as $item)
            {
                $count += ($item->getOrderItem()->getProduct()->getData($packageCountAttribute) * $item->getipi_qty());
            }
            return ($count > 0 ? $count : 1);
        }

        return 1;
    }


    public function hydrateWithOrderInformation()
    {
        foreach($this->getOrder()->getData() as $k => $v)
        {
            if (!$this->getData($k))
                $this->setData($k, $v);
        }
    }

    public function setForceCarrierTemplate($template)
    {
        $this->_forceCarrierTemplate = $template;
    }

    public function getCarrierTemplate()
    {
        //used to inject for dummy order object
        if ($this->_forceCarrierTemplate)
            return $this->_forceCarrierTemplate;

        if (!$this->_carrierTemplate)
        {
            $this->_carrierTemplate = $this->_carrierTemplateHelper->getCarrierTemplateForOrder($this, $this->getip_warehouse_id());
        }

        return $this->_carrierTemplate;
    }

    public function updateTotalWeight($save = true)
    {
        $weight = 0;
        foreach($this->getAllItems() as $item)
        {
            if ($item->getOrderItem()->getProduct())
                $weight += $item->getOrderItem()->getProduct()->getweight() * $item->getipi_qty();
        }

        $this->setip_total_weight($weight);
        if ($save)
            $this->save();
    }

    public function updateTotalVolume($save = true)
    {
        $volumeAttribute = $this->_configFactory->getVolumeAttribute();
        if (!$volumeAttribute)
            return;

        $volume = 0;
        foreach($this->getAllItems() as $item)
        {
            if ($item->getOrderItem()->getProduct())
                $volume += $item->getOrderItem()->getProduct()->getData($volumeAttribute) * $item->getipi_qty();
        }

        $this->setip_total_volume($volume);
        if ($save)
            $this->save();
    }

    public function getProductSkus()
    {
        $skus = [];

        if ($this->getId()) {
            foreach ($this->getAllItems() as $item) {
                $skus[] = $item->getOrderItem()->getSku();
            }
        }

        return implode(' - ', $skus);
    }

    public function getProductNames()
    {
        $names = [];

        if ($this->getId()){
            foreach($this->getAllItems() as $item)
            {
                $names[] = $item->getOrderItem()->getName();
            }
        }

        return implode(' - ', $names);
    }

    public function kiloToGram($value)
    {
        if (!$value)
            $value = 0;
        return (int)($value * 1000);
    }
    public function addParcelBoxes($boxDetails)
    {
        $this->setip_boxes($boxDetails);
        return $this;
    }

    public function isOrderAlreadyAdded($orderId, $warehouseId)
    {
        return $this->_getResource()->isOrderAlreadyAdded($orderId, $warehouseId);
    }

}
