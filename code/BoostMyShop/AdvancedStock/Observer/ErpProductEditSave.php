<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ErpProductEditSave implements ObserverInterface
{
    protected $_eventManager;
    protected $_stockMovementFactory;
    protected $_backendAuthSession;
    protected $_warehouseItemFactory;
    protected $_stockItemFactory;
    protected $_logger;
    protected $_productFactory;
    protected $_reservationFixer;
    protected $_barcodeFactory;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \BoostMyShop\AdvancedStock\Model\Warehouse\Item\ReservationFixer $reservationFixer,
        \BoostMyShop\AdvancedStock\Model\BarcodesFactory $barcodeFactory
    ) {
        $this->_eventManager = $eventManager;
        $this->_stockMovementFactory = $stockMovementFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_stockItemFactory = $stockItemFactory;
        $this->_logger = $logger;
        $this->_productFactory = $productFactory;
        $this->_reservationFixer = $reservationFixer;
        $this->_barcodeFactory= $barcodeFactory;
    }

    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $postData = $observer->getEvent()->getPostData();
        $messageManager = $observer->getEvent()->getmessage_manager();


        // update additional barcode
        $result = $this->_barcodeFactory->create()->getProductFilter($product->getId());
        foreach ($result as $value){
            if(count($result)>0){
                $this->deleteBarcodes($value['bac_id']);
            }
        }
        $this->saveAdditionalBarcodes($postData['barcodes'],$product->getId());

        //create stock movement
        $smData = (isset($postData['newStockMovement']) ? $postData['newStockMovement'] : false);
        $smAdditionalData = (isset($postData['newStockMovement']['additional']) ? $postData['newStockMovement']['additional'] : []);

        if ($smData && isset($smData['sm_qty']) && ($smData['sm_qty'] > 0)) {
            try
            {
                $this->createStockMovement($product, $smData, $smAdditionalData);
                $messageManager->addSuccess(__('Stock movement created.'));
            }
            catch(\Exception $ex)
            {
                $messageManager->addError(__('Unable to create stock movement : '.$ex->getMessage()));
            }
        }

        //update warehouse item data
        $wData = (isset($postData['warehouses']) ? $postData['warehouses'] : false);
        if ($wData)
        {
            foreach($wData as $warehouseItemId => $warehouseData)
            {
                $this->updateWarehouseItem($warehouseItemId, $warehouseData);
            }
        }

        //update stock item data
        $stockItemData = (isset($postData['stockItem']) ? $postData['stockItem'] : false);
        if ($stockItemData)
        {
            foreach($stockItemData as $stockItemId => $data)
            {
                $this->updateStockItem($stockItemId, $data);
            }
        }

        //stock allocation
        if (isset($postData['stock_allocation_from']) && isset($postData['stock_allocation_to']))
        {
            if ($postData['stock_allocation_from'] && $postData['stock_allocation_to']) {
                $qtyMoved = $this->_reservationFixer->moveReservedQuantity($postData['stock_allocation_from'], $postData['stock_allocation_to']);
                $messageManager->addSuccess(__('Stock allocation successfully processed : %1 items moved', $qtyMoved));
            }
        }

        return $this;
    }

    protected function createStockMovement($product, $smData, $smAdditionalData)
    {
        $userId = null;
        if ($this->_backendAuthSession->getUser())
            $userId = $this->_backendAuthSession->getUser()->getId();

        $this->_stockMovementFactory->create()->create( $product->getId(),
                                                        $smData['sm_from_warehouse_id'],
                                                        $smData['sm_to_warehouse_id'],
                                                        $smData['sm_qty'],
                                                        $smData['sm_category'],
                                                        $smData['sm_comments'],
                                                        $userId,
                                                        $smAdditionalData
                                                        );

        return $this;
    }

    protected function updateWarehouseItem($warehouseItemId, $warehouseData)
    {
        $warehouseItem = $this->_warehouseItemFactory->create()->load($warehouseItemId);

        $fields = ['wi_shelf_location', 'wi_use_config_warning_stock_level', 'wi_warning_stock_level', 'wi_use_config_ideal_stock_level', 'wi_ideal_stock_level'];
        foreach($fields as $field)
        {
            if (isset($warehouseData[$field]))
                $warehouseItem->setData($field, $warehouseData[$field]);
        }

        $warehouseItem->save();
    }

    protected function updateStockItem($stockItemId, $data)
    {
        $stockItem = $this->_stockItemFactory->create()->load($stockItemId);
        $hasChanges = false;

        if (isset($data['use_config_backorders']) && ($data['use_config_backorders'] != $stockItem->getuse_config_backorders()))
        {
            $stockItem->setuse_config_backorders($data['use_config_backorders']);
            $hasChanges = true;
        }

        if (!$stockItem->getuse_config_backorders() && isset($data['backorders']) && ($data['backorders'] != $stockItem->getbackorders()))
        {
            $stockItem->setbackorders($data['backorders']);
            $hasChanges = true;
        }

        if ($hasChanges)
        {
            if ($this->allowIsInStockChange($stockItem->getproduct_id()))
            {
                $hasQty = $stockItem->getQty() > $stockItem->getMinQty();
                if ($stockItem->getBackorders() && !$stockItem->getIsInStock())
                    $stockItem->setIsInStock(true);
                else
                {
                    if (!$stockItem->getBackorders() && !$hasQty && $stockItem->getIsInStock())
                        $stockItem->setIsInStock(false);
                    if (!$stockItem->getBackorders() && $hasQty && !$stockItem->getIsInStock())
                        $stockItem->setIsInStock(true);
                }

                $this->_logger->log('Change stock item #'.$stockItem->getId().' : isinstock='.($stockItem->getIsInStock() ? 1 : 0), \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventoryCore);

                $stockItem->save();
            }
            else
                $this->_logger->log('Do not allow to change isinstock for stockitem #'.$stockItem->getId(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventoryCore);
        }

    }

    protected function allowIsInStockChange($productId)
    {
        $product = $this->_productFactory->create()->load($productId);

        $deniedProductTypes = ['configurable', 'bundle', 'grouped', 'container'];
        if (in_array($product->getTypeId(), $deniedProductTypes))
            return false;
        else
            return true;
    }

    protected function saveAdditionalBarcodes($data,$productId)
    {
        if($data){
            foreach ($data as $key=>$value ){
                $barcode =  $this->_barcodeFactory->create();
                $barcode->setbac_product_id($productId)
                    ->setbac_code($value)
                    ->save();
            }
        }

    }

    protected function deleteBarcodes($id)
    {
        try{
            $barcode =  $this->_barcodeFactory->create();
            $barcode->load($id);
            $barcode->delete();
        }catch (\Exception $e){

        }

    }

}
