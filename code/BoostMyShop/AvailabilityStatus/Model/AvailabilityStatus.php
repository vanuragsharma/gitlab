<?php

namespace BoostMyShop\AvailabilityStatus\Model;

class AvailabilityStatus
{
    protected $_logger;
    protected $_config;
    protected $_stockRegistry;
    protected $_purchaseOrderProductCollectionFactory;
    protected $_supplierProductCollectionFactory;
    protected $_warehouseItemCollectionFactory;
    protected $_productFactory;
    protected $_storeFactory;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $purchaseOrderProductCollectionFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Product\CollectionFactory $supplierProductCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \BoostMyShop\AvailabilityStatus\Helper\Logger $logger,
        \BoostMyShop\AvailabilityStatus\Model\Config $config
    ){
        $this->_config = $config;
        $this->_logger = $logger;
        $this->_storeFactory = $storeFactory;
        $this->_stockRegistry = $stockRegistry;
        $this->_purchaseOrderProductCollectionFactory = $purchaseOrderProductCollectionFactory;
        $this->_supplierProductCollectionFactory = $supplierProductCollectionFactory;
        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
        $this->_productFactory = $productFactory;
    }

    /**
     * @param $product
     * @param $storeId
     * @return array|bool : array with keys date & message (string)
     */
    public function getAvailability($productId, $storeId, $forceWarehouseIds = null, $forceQty = null)
    {
        if (is_object($productId))
            $productId = $productId->getId();

        $store = $this->_storeFactory->create()->load($storeId);
        $websiteId = $store->getwebsite_id();

        if (!$this->productIsAvailable($productId))
        {
            return $this->getOutOfStockMessage($productId, $storeId);
        }

        if ($forceQty > 0 || ($forceQty === null && $this->getStockQty($productId, $websiteId, $storeId) > 0))
        {
            if (!$this->_config->getUseWarehouseDelay($storeId))
                return $this->getInStockMessage($productId, $storeId);
            else
            {
                return $this->getInStockWithWarehouseDelayMessage($productId, $storeId, $forceWarehouseIds);
            }
        }

        return $this->getBackorderMessage($productId, $storeId);
    }

    public function productIsAvailable($productId)
    {
        $product = $this->_productFactory->create()->load($productId);
        return $product->isAvailable();
    }


    protected function getInStockMessage($productId, $storeId)
    {
        $result = [];
        $result['date'] = date('Y-m-d');

        $css = $this->_config->getSetting('instock/css', $storeId);
        $label = $this->_config->getSetting('instock/label', $storeId);
        $result['message'] = '<div class="'.$css.'">'.$label.'</div>';

        $this->_logger->log('Product #'.$productId.' : date='.$result['date'].', message='.strip_tags($result['message']));

        return $result;
    }

    public function getOutOfStockMessage($productId, $storeId)
    {
        $result = [];

        $css = $this->_config->getSetting('outofstock/css', $storeId);
        if ($this->_config->getSetting('outofstock/use_po', $storeId))
            $result = $this->getPoMessage($productId, $storeId, 'outofstock');

        if (!isset($result['message']))
        {
            $label = $this->_config->getSetting('outofstock/label', $storeId);
            $result['message'] =  $label;
            $result['date'] = date('Y-m-d', time() + 3600 * 24 * 365);
        }

        $result['message'] =  '<div class="'.$css.'">'.$result['message'].'</div>';

        $this->_logger->log('Product #'.$productId.' : date='.$result['date'].', message='.strip_tags($result['message']));

        return $result;
    }

    protected function getBackorderMessage($productId, $storeId)
    {
        $result = false;

        if ($this->_config->getSetting('backorder/use_po', $storeId))
            $result = $this->getPoMessage($productId, $storeId);

        if (($this->_config->getSetting('backorder/use_lead_time', $storeId)) && ($result && !$result['message']))
            $result = $this->getLeadTimeMessage($productId, $storeId);

        $css = $this->_config->getSetting('backorder/css', $storeId);
        if (!isset($result['message'])) {
            $result = [];
            $result['date'] = date('Y-m-d', time() + 3600 * 24 * 180);
            $result['message'] = '<div class="'.$css.'">'.$this->_config->getSetting('backorder/label', $storeId).'</div>';

            $this->_logger->log('Product #'.$productId.' : date='.$result['date'].', message='.strip_tags($result['message']));
        }
        else
            $result['message'] = '<div class="'.$css.'">'.$result['message'].'</div>';

        return $result;
    }

    /**
     * retrieve warehosues available for sales and having stock for this products
     * Return the message from the warehouse having the shortest delay
     *
     * @param $product
     * @param $storeId
     */
    protected function getInStockWithWarehouseDelayMessage($productId, $storeId, $forceWarehouseIds)
    {
        $shortestRecord = null;

        //get shortest warehouse
        $collection = $this->_warehouseItemCollectionFactory->create()
            ->addProductFilter($productId)
            ->addInStockFilter()
            ->joinWarehouse();

        if ($forceWarehouseIds)
            $collection->addFieldToFilter('wi_warehouse_id', ['in' => $forceWarehouseIds]);

        foreach($collection as $item)
        {
            if ($shortestRecord == null || $shortestRecord->getw_availability_delay() > $item->getw_availability_delay())
                $shortestRecord = $item;
        }

        $result = [];

        if ($shortestRecord)
        {
            $result['date'] = date('Y-m-d', time() + 3600 * 24 * $shortestRecord->getw_availability_delay());
            $result['message'] = $this->getWarehouseDelayMessage($shortestRecord->getw_availability_delay(), $storeId);
        }
        else
        {
            $result['date'] = 'undefined';
            $result['message'] = 'undefined';
        }

        return $result;
    }

    protected function getPoMessage($productId, $storeId, $stockMode = 'backorder')
    {
        $collection = $this->_purchaseOrderProductCollectionFactory
                            ->create()
                            ->addProductFilter($productId)
                            ->addExpectedFilter()
                            ->addRealEta()
                            ->addOrderStatusFilter(\BoostMyShop\Supplier\Model\Order\Status::expected)
                            ->addFieldToFilter('po_eta', ['gt' => date('Y-m-d')])
                            ->setOrder('po_eta', 'ASC');

        //if we have PO expected, we calculate the quantity to ship without stock so we can "ignore" PO that will be allocated to existing sales orders
        $quantityNeededForOrders = 0;
        if (count($collection) > 0)
            $quantityNeededForOrders = $this->getQuantityNeededForOrders($productId);

        foreach($collection as $item)
        {
            $result = [];
            $result['date'] = $item->getreal_eta();

            if ($quantityNeededForOrders < $item->getPendingQty())
            {
                $label = $this->_config->getSetting($stockMode.'/po_label', $storeId);
                $eta = strtotime($item->getreal_eta());
                $letters = ['d','D','j','l','N','S','w','z','W','F','m','M','n','t','Y','y','r'];
                foreach($letters as $letter)
                    $label = str_replace('{'.$letter.'}', date($letter, $eta), $label);

                $result['message'] = $label;

                $this->_logger->log('Product #'.$productId.' : use PO #'.$item->getpop_po_id());
                $this->_logger->log('Product #'.$productId.' : date='.$result['date'].', message='.strip_tags($result['message']));

                return $result;
            }
            else
                $quantityNeededForOrders -= $item->getPendingQty();
        }

        $this->_logger->log('Product #'.$productId.' : no PO available');

        return false;
    }

    protected function getLeadTimeMessage($productId, $storeId)
    {
        $collection = $this->_supplierProductCollectionFactory->create()->getSuppliers($productId);

        foreach($collection as $item)
        {
            if ($item->getsp_shipping_delay()) {
                $shippingDelay = $item->getsp_shipping_delay();
            } else {
                $shippingDelay = $item->getsup_shipping_delay();
            }

            if ($shippingDelay)
            {
                $result = [];
                $result['date'] = date('Y-m-d', time() + 3600 * 24 * $shippingDelay);
                $result['message'] = $this->getLeadTimeRangeMessage($shippingDelay, $storeId);

                $this->_logger->log('Product #'.$productId.' : use supplier #'.$item->getsup_id());
                $this->_logger->log('Product #'.$productId.' : date='.$result['date'].', message='.strip_tags($result['message']));

                return $result;
            }

        }

        $this->_logger->log('Product #'.$productId.' : no supplier available');

        return false;
    }

    protected function getStockQty($productId, $websiteId, $forceWarehouseIds)
    {
        $value = $this->_stockRegistry->getStockStatus($productId, $websiteId)->getQty();
        $this->_logger->log('Product #'.$productId.' : stock='.$value);
        return $value;
    }

    protected function getLeadTimeRangeMessage($leadTime, $storeId)
    {
        for($i=0;$i<10;$i++)
        {
            $from = $this->_config->getSetting('backorder/from_'.$i, $storeId);
            $to = $this->_config->getSetting('backorder/to_'.$i, $storeId);
            if (($from <= $leadTime) && ($leadTime <= $to))
                return $this->_config->getSetting('backorder/message_'.$i, $storeId);
        }
    }

    protected function getWarehouseDelayMessage($delay, $storeId)
    {
        for($i=0;$i<10;$i++)
        {
            $from = $this->_config->getSetting('instock/from_'.$i, $storeId);
            $to = $this->_config->getSetting('instock/to_'.$i, $storeId);
            if (($from <= $delay) && ($delay <= $to))
                return $this->_config->getSetting('instock/message_'.$i, $storeId);
        }

        //return default in stock message
        return $this->_config->getSetting('instock/label', $storeId);
    }

    protected function getQuantityNeededForOrders($productId)
    {
        $quantityNeededForOrders = 0;

        $warehouseItems = $this->_warehouseItemCollectionFactory->create()
                                ->addProductFilter($productId)
                                ->addBackorderFilter();
        foreach($warehouseItems as $warehouseItem)
        {
            $quantityNeededForOrders += max($warehouseItem->getwi_quantity_to_ship() - $warehouseItem->getwi_physical_quantity(), 0);
        }

        return $quantityNeededForOrders;
    }

}
