<?php

namespace BoostMyShop\OrderPreparation\Model\InProgress;

class Shipment
{
    protected $_shipmentFactory;
    protected $_shipmentSender;
    protected $_trackFactory;
    protected $_logger;
    protected $_orderFactory;

    public function __construct(
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \BoostMyShop\OrderPreparation\Helper\Transaction $transaction,
        \BoostMyShop\OrderPreparation\Helper\Logger $logger
    ) {
        $this->_shipmentFactory = $shipmentFactory;
        $this->_transaction = $transaction;
        $this->_shipmentSender = $shipmentSender;
        $this->_trackFactory = $trackFactory;
        $this->_logger = $logger;
        $this->_orderFactory = $orderFactory;
    }

    public function createShipment($inProgress, $shipmentItems = null, $userId = -1)
    {
        //reload order to prevent erpcloud prefix removal
        $order = $inProgress->getOrder();
        $order = $this->_orderFactory->create()->load($order->getId());

        if ($shipmentItems == null)
            $shipmentItems = $this->prepareShipmentItems($inProgress);

        $this->appendParents($inProgress, $shipmentItems);

        foreach($shipmentItems as $k => $v)
            $this->_logger->log('Add '.$v.'x item #'.$k.' to ship order #'.$inProgress->getOrder()->getIncrementId());

        $shipment = $this->_shipmentFactory->create($order, $shipmentItems, []);
        $shipment->setpacker_user_id($userId);
        $shipment->setCreatedFromInProgress(1);
        if($inProgress->getip_dummy_shipment_increment_id())
            $shipment->setdummy_increment_id($inProgress->getip_dummy_shipment_increment_id());
        $shipment->register();

        $this->_transaction->reset();
        $transactionSave = $this->_transaction->addObject($order);
        $transactionSave->addObject($shipment);
        $transactionSave->save();

        return $shipment;
    }

    public function addTracking($shipment, $trackingNumber, $carrierCode = '', $title = '')
    {
        if (!$title)
            $title = $shipment->getOrder()->getshipping_description();
        if (!$carrierCode)
        {
            $method = '';
            list($carrierCode, $method) = explode('_', $shipment->getOrder()->getshipping_method(), 2);
        }
        $data = ['carrier_code' => $carrierCode, 'title' => $title, 'number' => $trackingNumber];

        $shipment->addTrack($this->_trackFactory->create()->addData($data))->save();

        return $shipment;
    }

    public function notifyCustomer($shipment)
    {
        $this->_shipmentSender->send($shipment);
    }

    /**
     *
     * @param $inProgress
     * @return array
     */
    protected function prepareShipmentItems($inProgress)
    {
        $items = [];

        foreach($inProgress->getAllItems() as $item)
        {
            $items[$item->getitem_id()] = $item->getipi_qty();

            if ($item->shipWithParent())
            {
                $items[$item->getOrderItem()->getParentItemId()] = $item->getipi_qty();
            }
        }

        return $items;
    }

    protected function appendParents($inProgress, &$shipmentItems)
    {
        foreach($inProgress->getAllItems() as $item)
        {
            if (isset($shipmentItems[$item->getitem_id()]) && ($shipmentItems[$item->getitem_id()] > 0))
            {
                if ($item->shipWithParent() && (!isset($shipmentItems[$item->getOrderItem()->getParentItemId()])))
                    $shipmentItems[$item->getOrderItem()->getParentItemId()] = $item->getipi_qty();

            }
        }
    }
}