<?php

namespace BoostMyShop\OrderPreparation\Model;

class OrderPreparation
{
    protected $_inProgressFactory;
    protected $_inProgressCollectionFactory;
    protected $_invoiceHelperFactory;
    protected $_config;
    protected $_registry;
    protected $_orderItemFactory;
    protected $_logger;
    protected $_eventManager;
    protected $_inStockOrdersFactory = null;
    protected $_inProgressOrdersFactory = null;
    protected $_orderFactory;

    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \BoostMyShop\OrderPreparation\Model\InProgressFactory $inProgressFactory,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressCollectionFactory,
        \BoostMyShop\OrderPreparation\Model\Registry $registry,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Sales\Model\Order\ItemFactory $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \BoostMyShop\OrderPreparation\Model\InProgress\InvoiceFactory $invoiceHelperFactory,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        \BoostMyShop\OrderPreparation\Block\Preparation\Tab\InStockFactory $inStockOrdersFactory,
        \BoostMyShop\OrderPreparation\Block\Preparation\InProgressFactory $inProgressOrdersFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ){
        $this->_inProgressFactory = $inProgressFactory;
        $this->_inProgressCollectionFactory = $inProgressCollectionFactory;
        $this->_config = $config;
        $this->_registry = $registry;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_invoiceHelperFactory = $invoiceHelperFactory;
        $this->_logger = $logger;
        $this->_eventManager = $eventManager;
        $this->_inStockOrdersFactory = $inStockOrdersFactory;
        $this->_inProgressOrdersFactory = $inProgressOrdersFactory;
        $this->_orderFactory = $orderFactory;
    }


    public function getItemsToShip($order, $warehouseId)
    {
        $items = [];

        foreach($order->getAllItems() as $orderItem)
        {
            switch($orderItem->getproduct_type())
            {
                case 'simple':
                case 'virtual':
                case 'grouped':
                case 'downloadable':
                    $parentItem = null;
                    if ($orderItem->getparent_item_id())
                        $parentItem = $this->_orderItemFactory->create()->load($orderItem->getparent_item_id());
                    if(isset($parentItem)){
                        if($parentItem->getproduct_type() == "bundle"){
                            $qtyToShip = $parentItem->getQtyToShip() * $orderItem->getQtyToShip();
                        }
                        else
                            $qtyToShip = $parentItem->getQtyToShip();
                    }else{
                        $qtyToShip = $orderItem->getQtyToShip();
                    }
                    if ($qtyToShip > 0)
                        $items[$orderItem->getId()] = $qtyToShip;
                    break;
                default:
                    //nothing, we dont handle parent items
                    break;
            }
        }

        return $items;
    }

    /**
     * @param $order
     * @param array $orderItems
     * @param $userId
     */
    public function addOrder($order, $orderItems = [], $userId, $warehouseId)
    {

        if (!is_array($orderItems) || count($orderItems) == 0)
            $orderItems = $this->getItemsToShip($order, $warehouseId);

        if (is_array($orderItems) && count($orderItems) == 0)
            throw new \Exception('This order can not be shipped');

        //check if order already added
        if (!$this->_config->isBatchEnable() && $this->_inProgressFactory->create()->isOrderAlreadyAdded($order->getId(), $warehouseId))
            throw new \Exception('Order already added to preparation');

        $invoiceId = $this->getExistingInvoiceId($order);

        $obj = $this->_inProgressFactory->create();
        $obj->setip_order_id($order->getId());
        $obj->setip_user_id($userId);
        $obj->setip_warehouse_id($warehouseId);
        $obj->setip_store_id($order->getStoreId());
        $obj->setip_invoice_id($invoiceId);
        $obj->setip_status(\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_NEW);
        $obj->save();

        //add order item
        foreach($orderItems as $orderItemId => $qty)
            $obj->addProduct($orderItemId, $qty);

        //update totals
        $obj->updateTotalWeight();
        $obj->updateTotalVolume();

        //automatically invoice order if configured
        try
        {
            if ($this->_config->createInvoiceWhenAddedToInProgress() && $order->canInvoice())
            {
                $invoice = $this->_invoiceHelperFactory->create()->createInvoice($obj, []);
                $obj->setip_invoice_id($invoice->getId())->save();
            }
        }
        catch(\Exception $ex)
        {
            //exception should be about order capture
            $obj->delete();

            //append comment to the order
            $order->addStatusToHistory($order->getstatus(), __('Unable to create invoice : %1', $ex->getMessage()))->save();

            throw new \Exception(__('Unable to create invoice for order %1: %2', $order->getincrement_id(), $ex->getMessage()));
        }

        //raise event
        $this->_eventManager->dispatch('bms_order_preparation_order_added_to_in_progress', ['in_progress' => $obj]);

        return $obj;
    }

    /**
     * @param $orderId
     */
    public function remove($inProgressId)
    {
        $obj = $this->_inProgressFactory->create()->load($inProgressId);
        $obj->delete();

        return $this;
    }

    /**
     * @return $this
     */
    public function flush()
    {
        $collection = $this->_inProgressCollectionFactory
            ->create()
            ->addUserFilter($this->_registry->getCurrentOperatorId());

        if($this->_config->getFlushPackedOrders()) {
            $collection->addFieldToFilter('ip_status', array('in' => [
                \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PACKED,
                \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED
            ]));
        }
        else
            $collection->addFieldToFilter('ip_status', \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED);

        foreach($collection as $item)
        {
            $item->delete();
        }

        return $this;
    }

    public function massCreate($createShipment, $createInvoice, $warehouseId)
    {
        $errors = [];

        $collection = $this->_inProgressCollectionFactory
                            ->create()
                            ->addFieldToFilter('ip_status', \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_NEW)
                            ->addWarehouseFilter($warehouseId)
                            ->addUserFilter($this->_registry->getCurrentOperatorId())
                            ;
        foreach($collection as $item)
        {
            try
            {
                $item->pack($createShipment, $createInvoice);
            }
            catch(\Exception $ex)
            {
                $errors[] = 'Error for order #'.$item->getOrder()->getIncrementId().' : '.$ex->getMessage();
            }

        }

        return $errors;
    }

    public function getExistingInvoiceId($order)
    {
        foreach($order->getInvoiceCollection() as $invoice)
            return $invoice->getId();
    }

    public function populateBinCart($warehouseId, $userId)
    {
        $ipCollection = $this->_inProgressOrdersFactory->create()->getPreparedCollection();

        if ($ipCollection->getSize() > 0)
            throw new \Exception(__("Some orders are already in progress"));

        $cartbinsize = $this->_config->getCartBinSize();
        $collection = $this->_inStockOrdersFactory->create()->getInStockCollection($cartbinsize);
        $count = 0;
        foreach ($collection as $key => $orderObj) {
            $order = $this->_orderFactory->create()->load($orderObj->getId());
            $this->addOrder($order, [], $userId, $warehouseId);
            $count++;
        }

        return $count;
    }
}
