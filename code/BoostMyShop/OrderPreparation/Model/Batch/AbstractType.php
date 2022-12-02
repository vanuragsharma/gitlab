<?php
namespace BoostMyShop\OrderPreparation\Model\Batch;

abstract class AbstractType
{
    protected $_orders;
    protected $_ordersCollection;
    protected $_ordersFactory = null;
    protected $_inStockTab;
    protected $_config = null;
    protected $_inProgressCollectionFactory;
    protected $_preparationRegistry;
    protected $_urlBuilder;

    public function __construct(
        \BoostMyShop\OrderPreparation\Model\ResourceModel\Order\CollectionFactory $ordersFactory,
        \BoostMyShop\OrderPreparation\Block\Preparation\Tab\InStock $inStockTab,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressCollectionFactory,
        \Magento\Framework\UrlInterface $urlBuilder
    ){
        $this->_ordersFactory = $ordersFactory;
        $this->_inStockTab = $inStockTab;
        $this->_config = $config;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_inProgressCollectionFactory = $inProgressCollectionFactory;
        $this->_urlBuilder = $urlBuilder;
    }

    public function getAllowedOrderStatuses()
    {
        return $this->_config->getOrderStatusesForTab('instock');
    }

    public function isBatchEnable()
    {
        return $this->_config->isBatchEnable();
    }

    public function getUniqueProductMinimumOrderCount()
    {
        return $this->_config->getUniqueProductMinimumOrderCount();
    }

    protected function getOrders($warehouseId = 0)
    {
        if(!$this->_ordersCollection)
        {
            $this->_ordersCollection = $this->_ordersFactory->create();
            $this->_ordersCollection->addAdditionalFields();
            $this->_ordersCollection->addFieldToFilter('main_table.status', ['in' => $this->getAllowedOrderStatuses()]);

            //exclude orders being prepared
            $selectedOrderIds = $this->_inProgressCollectionFactory->create()->getOrderIds($this->_preparationRegistry->getCurrentWarehouseId());
            if (count($selectedOrderIds) > 0)
                $this->_ordersCollection->addFieldToFilter('main_table.entity_id', array('nin' => $selectedOrderIds));

            //add filter on warehouse
            $warehouseId = $warehouseId?$warehouseId:$this->_preparationRegistry->getCurrentWarehouseId();
            $this->_inStockTab->addWarehouseFilter($this->_ordersCollection, $warehouseId);

            $this->_inStockTab->addAdditionnalFilters($this->_ordersCollection);

            $this->_ordersCollection->getSelect()->joinLeft(
                ['soi' => $this->_ordersCollection->getTable('sales_order_item')],
                "soi.order_id = so.entity_id and so.total_item_count = 1",
                ['product_id', 'qty_ordered']);

            $this->_ordersCollection->getSelect()->group("main_table.entity_id");
        }
        return $this->_ordersCollection;
    }

    public function getCandidateOrderCount($warehouseId, $carrier = null)
    {
        return count($this->getCandidateOrders($warehouseId, $carrier));
    }

    public function getCandidateOrdersForAllInstance($warehouseId, $carrier = null)
    {
        $orders = [];
        $collection = $this->getOrders($warehouseId);

        foreach ($collection as $order) {
            if($carrier) {
                if(!$this->orderMatchesToCarrier($order, $carrier))
                {
                    continue;
                }
            }
            $orders[] = $order;
        }

        return $orders;
    }

    public function getAdditionalActions($batch)
    {
        return [];
    }

    public function orderMatchesToCarrier($order, $carrier)
    {
        $method = explode("_", $order->getShippingMethod());
        $orderCarrier = $method[0];
        if ($orderCarrier != $carrier)
        {
            if(count($method) > 2)
                $orderCarrier = $method[0]."_".$method[1];

            if($orderCarrier != $carrier)
                return false;
        }

        return true;
    }

    public abstract function getCode();

    public abstract function getName();

    public abstract function getCandidateOrders($warehouseId, $carrier);

    public abstract function getPdfClass();
}