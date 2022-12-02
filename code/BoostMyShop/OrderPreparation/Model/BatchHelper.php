<?php
namespace BoostMyShop\OrderPreparation\Model;

class BatchHelper
{
    protected $_uniqueProduct;
    protected $_singleProduct;
    protected $_multipleProduct;
    protected $_allProduct;
    protected $_ordersFactory = null;
    protected $_inStockTab;
    protected $_ordersCollection;
    protected $_batchFactory;
    protected $_orderFactory;
    protected $_currentWhId = null;
    protected $_carrierHelper;
    protected $_alllCrriers = null;
    protected $_date;
    protected $_config;



    public function __construct(
        \BoostMyShop\OrderPreparation\Model\Batch\Type\UniqueProduct $uniqueProduct,
        \BoostMyShop\OrderPreparation\Model\Batch\Type\SingleProduct $singleProduct,
        \BoostMyShop\OrderPreparation\Model\Batch\Type\MultipleProduct $multipleProduct,
        \BoostMyShop\OrderPreparation\Model\Batch\Type\AllProduct $allProduct,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\Order\CollectionFactory $ordersFactory,
        \BoostMyShop\OrderPreparation\Block\Preparation\Tab\InStock $inStockTab,
        \BoostMyShop\OrderPreparation\Model\BatchFactory $batchFactory,
        \BoostMyShop\OrderPreparation\Helper\Carrier $carrierHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \BoostMyShop\OrderPreparation\Model\Config $config
    ){
        $this->_uniqueProduct = $uniqueProduct;
        $this->_singleProduct = $singleProduct;
        $this->_multipleProduct = $multipleProduct;
        $this->_allProduct = $allProduct;
        $this->_ordersFactory = $ordersFactory;
        $this->_inStockTab = $inStockTab;
        $this->_batchFactory = $batchFactory;
        $this->_orderFactory = $orderFactory;
        $this->_carrierHelper = $carrierHelper;
        $this->_date = $date;
        $this->_config = $config;
    }

    protected function getOrdersCollection()
    {
        if(!$this->_ordersCollection) {
            $this->_ordersCollection = $this->_ordersFactory->create();
            $this->_ordersCollection->addAdditionalFields();
            $this->_inStockTab->addAdditionnalFilters($this->_ordersCollection);
        }
        return $this->_ordersCollection;
    }

    public function getCurrentWhId()
    {
        if(!$this->_currentWhId) {
            $batch = $this->_batchFactory->create();
            $this->_currentWhId = $batch->getCurrentWarehouseId();
        }
        return $this->_currentWhId;
    }

    public function getAllAcrriers()
    {
        if(!$this->_alllCrriers)
        {
            foreach($this->_carrierHelper->getAllCarriers() as $carrier)
            {
                $this->_alllCrriers[$carrier->getId()] = $carrier->getName();
            }
            asort($this->_alllCrriers);
        }

        return $this->_alllCrriers;
    }

    public function getCarriers($warehouseId)
    {
        $allCarriers = $this->getAllAcrriers();
        $allMethod = [];
        foreach ($this->_uniqueProduct->getCandidateOrdersForAllInstance($this->getCurrentWhId()) as $order)
        {
            $method = explode("_", $order->getShippingMethod());
            $carrier = $method[0];

            if(!isset($allCarriers[$carrier]) && count($method) > 2)
            {
                $carrier = $method[0]."_".$method[1];
            }
            $allMethod[$carrier] = isset($allCarriers[$carrier])?$allCarriers[$carrier]:$order->getShippingInformation();
        }

        return $allMethod;
    }

    public function getTypes()
    {
        $types[$this->_uniqueProduct->getCode()] = $this->_uniqueProduct->getName();
        $types[$this->_singleProduct->getCode()] = $this->_singleProduct->getName();
        $types[$this->_multipleProduct->getCode()] = $this->_multipleProduct->getName();
        return $types;
    }
    public function getTypeInstance($typeCode)
    {
        $typeInstance = null;
        switch($typeCode)
        {
            case \BoostMyShop\OrderPreparation\Model\Batch\Type\UniqueProduct::CODE:
                $typeInstance = $this->_uniqueProduct;
                break;
            case \BoostMyShop\OrderPreparation\Model\Batch\Type\SingleProduct::CODE:
                $typeInstance = $this->_singleProduct;
                break;
            case \BoostMyShop\OrderPreparation\Model\Batch\Type\MultipleProduct::CODE:
                $typeInstance = $this->_multipleProduct;
                break;
            case \BoostMyShop\OrderPreparation\Model\Batch\Type\AllProduct::CODE:
                $typeInstance = $this->_allProduct;
                break;
        }

        if (!$typeInstance)
            return null;
        else {
            return $typeInstance;
        }
    }

    public function createNewBatch($warehouseId, $type, $carrier = null, $orderIds = [])
    {
        $typeInstance = $this->getTypeInstance($type);
        if($typeInstance) {
            $maxOrdersCount = null;
            switch($type)
            {
                case 'unique':
                    $maxOrdersCount = $this->_config->maxOrdersCountInUniqueBatch();
                    break;
                case 'single':
                    $maxOrdersCount = $this->_config->maxOrdersCountInSingleBatch();
                    break;
                case 'multiple':
                    $maxOrdersCount = $this->_config->maxOrdersCountInMultipleBatch();
                    break;
                case 'all':
                    $maxOrdersCount = $this->_config->maxOrdersCountInAllBatch();
                    break;
            }

            if(count($orderIds) == 0)
                $orderIds = $typeInstance->getCandidateOrders($this->getCurrentWhId(), $carrier);

            $orderIdsToManage = array_slice($orderIds, 0, (int)$maxOrdersCount);

            if(count($orderIdsToManage) == 0)
                throw new \Exception(__("No order found to create Batch"));

            $batch = $this->_batchFactory->create()
                ->setbob_warehouse_id($warehouseId)
                ->setbob_created_at($this->_date->gmtDate())
                ->setbob_order_count(0)
                ->setbob_product_count(0)
                ->setbob_progress(0)
                ->setbob_label(date("Ymdhis"))
                ->setbob_carrier($carrier)
                ->setbob_type($type)
                ->setbob_status(\BoostMyShop\OrderPreparation\Model\Batch::STATUS_NEW)
                ->save();

            foreach ($orderIdsToManage as $orderId) {
                $order = $this->_orderFactory->create()->load($orderId);
                $batch->addOrder($order);
            }

            if (!$this->_config->getBatchDisableLabelPregeneration())
                $batch->updateStatus(\BoostMyShop\OrderPreparation\Model\Batch::STATUS_READY_FOR_LABEL_GENERATION);
            else
                $batch->updateStatus(\BoostMyShop\OrderPreparation\Model\Batch::STATUS_READY);

            return $batch;
        }
        else
        {
            throw new \Exception(_("Type %1 not valid", $type));
        }
    }
}