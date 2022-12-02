<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

use Magento\Backend\App\Action\Context;

class Index extends AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/Index.phtml';

    public function getOrdersInProgress()
    {
        if($this->isBatchEnable())
        {
            if ($this->getCurrentBatch()->getId())
                return $this->getCurrentBatch()->getBatchOrders();
            else
                return [];
        }
        else {
            $userId = $this->_preparationRegistry->getCurrentOperatorId();
            $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();
            return $this->_inProgressFactory->create()->addOrderDetails()->addUserFilter($userId)->addWarehouseFilter($warehouseId);
        }
    }

    public function getActiveBatches()
    {
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();
        return $this->_batchFactory->create()->getCollection()->addActiveFilter()->addWarehouseFilter($warehouseId);
    }

    public function getSelectOrderByIdUrl()
    {
        return $this->getUrl('*/*/*', ['order_id' => 'param_order_id']);
    }

    public function getSelectBatchByIdUrl()
    {
        return $this->getUrl('*/*/*', ['batch_id' => 'param_batch_id']);
    }

    public function getSaveItemUrl()
    {
        return $this->getUrl('*/*/saveItem');
    }

    public function getItemCustomOptionsFormUrl()
    {
        return $this->getUrl('*/*/productCustomOptions');
    }

    public function getItemIdsAsJson()
    {
        $ids = array();
        if ($this->hasOrderSelect())
        {
            foreach($this->currentOrderInProgress()->getAllItems() as $item)
            {
                $ids[] = $item->getId();
            }
        }
        return json_encode($ids);
    }

    public function getOrdersAsJson()
    {
        $ids = array();
        $enableImprovedMode = true;

        if($this->isBatchEnable() && $this->getCurrentBatch()->getId() && $enableImprovedMode)
        {
            $tempIds = [];
            $collection = $this->_inProgressItemCollectionFactory
                                        ->create()
                                        ->joinInProgress()
                                        ->joinOrderItem()
                                        ->joinOrder()
                                        ->joinBarcode($this->_config->getBarcodeAttribute())
                                        ->addFieldToFilter('ip_batch_id', $this->getCurrentBatch()->getId());
            $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns(['so.increment_id', 'bms_orderpreparation_inprogress.ip_id', 'bms_orderpreparation_inprogress.ip_status', 'cpev.value']);
            foreach($collection as $item)
            {
                if (!isset($tempIds[$item->getip_id()]))
                    $tempIds[$item->getip_id()] = ['increment_id' => $item->getincrement_id(), 'status' =>  $item->getip_status(), 'products' => []];
                if ($item->getValue())
                    $tempIds[$item->getip_id()]['products'][] = $item->getValue();
            }

            foreach($tempIds as $k => $v)
            {
                $ids[$k] = [$v['increment_id'], $v['status'], $v['products']];
            }
        }
        else
        {
            if (!$this->isBatchEnable())
            {
                $oderByProBar = $this->_config->getPackOrderByProducBarcode();
                foreach($this->getOrdersInProgress() as $item) {
                    $product_barcodes = array();
                    if ($oderByProBar) {
                        foreach ($item->getAllItems() as $product) {
                            $productId = $product->getproductId();
                            $product_barcodes[] = $this->opProduct->getBarcode($productId);
                        }
                    }
                    $ids[$item->getId()] = [$item->getOrder()->getincrement_id(), $item->getip_status(), $product_barcodes];
                }
            }
        }

        return json_encode($ids);
    }

    public function getMode()
    {
        if (!$this->getCurrentBatch()->getId() && $this->isBatchEnable())
            return 'search_batch';
        else if (!$this->hasOrderSelect())
            return 'search_order';
        elseif ($this->currentOrderInProgress()->getip_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_NEW || $this->currentOrderInProgress()->getip_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PICKED)
            return 'pack_order';
        else
            return 'confirmation';
    }

    public function getAllowPartialPacking()
    {
        return ($this->_config->getAllowPartialPacking() ? 1 : 0);
    }

    public function getPackOrderByProducBarcode()
    {
        return ($this->_config->getPackOrderByProducBarcode() ? 1 : 0);
    }

    public function getLargeOrderMode()
    {
        return ($this->_config->getLargeOrderMode($this->getCurrentWebsiteId()) ? 1 : 0);
    }

    public function getBatchAsJson()
    {
        $ids = array();
        foreach($this->getActiveBatches() as $batch) {
            $ids[$batch->getId()] = $batch->getbob_label();
        }
        return json_encode($ids);
    }

    public function getAutoCommit()
    {
        return ($this->_config->getAutoCommit($this->getCurrentWebsiteId()) ? 1 : 0);
    }

}