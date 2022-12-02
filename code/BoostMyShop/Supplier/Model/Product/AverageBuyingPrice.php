<?php

namespace BoostMyShop\Supplier\Model\Product;

class AverageBuyingPrice
{
    protected $_receptionItemCollectionFactory;
    protected $_logger;

    public function __construct(
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Reception\Item\CollectionFactory $receptionItemCollectionFactory,
        \BoostMyShop\Supplier\Helper\Logger $logger
    ){
        $this->_receptionItemCollectionFactory = $receptionItemCollectionFactory;
        $this->_logger = $logger;
    }


    public function  calculateValue($productId, $quantity, $warehouseId = null)
    {
        $sum = 0;
        $count = 0;

        $receptions = $this->getReceptions($productId, $warehouseId);
        $this->_logger->log('Calculate cost for product #'.$productId.' and warehouse #'.$warehouseId.' : '.count($receptions).' receptions found');
        foreach($receptions as $item)
        {
            $buyingPrice = number_format(($item->getpop_price_base() + $item->getpop_extended_cost_base()) / $item->getpop_qty_pack() * (1 - $item->getpo_global_discount() / 100) * (1 - $item->getpop_discount_percent() / 100), 4, '.', '');
            $quantityToUse = min($quantity, ($item->getpop_qty_received() * $item->getpop_qty_pack()));
            if ($quantityToUse > 0)
            {
                $sum += $quantityToUse * $buyingPrice;
                $count += $quantityToUse;
                $quantity -= $quantityToUse;
            }
        }

        if ($count > 0)
            return ($sum / $count);

    }

    protected function getReceptions($productId, $warehouseId)
    {
        $receptions = $this->_receptionItemCollectionFactory->create()->addProductFilter($productId)->addOrderProductDetails()->setOrder('pori_id');

        if ($warehouseId)
            $receptions->addFieldToFilter('po_warehouse_id', $warehouseId);

        //limit to 10 receptions max
        $receptions->getSelect()->limit(10);

        return $receptions;
    }

}