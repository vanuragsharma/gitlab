<?php namespace BoostMyShop\AdvancedStock\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class LowStockLevelUpdater
{
    protected $_warehouseCollectionFactory;
    protected $_lowStockCollectionFactory;
    protected $_logger;
    protected $_warehouseItemFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\LowStock\CollectionFactory $lowStockCollectionFactory,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger
    )
    {
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_lowStockCollectionFactory = $lowStockCollectionFactory;
        $this->_logger = $logger;
        $this->_warehouseItemFactory = $warehouseItemFactory;
    }

    public function run()
    {
        $total = 0;
        $warehouses = $this->getWarehouses();
        foreach($warehouses as $warehouse)
        {
            $this->_logger->log('Process whs '.$warehouse->getId(), 'lowstocklevel');
            $total += $this->runWarehouse($warehouse);
        }
        return $total;
    }

    public function runWarehouse($warehouse)
    {
        $total = 0;

        //list products having disable_lowstock_update enabled
        $productsToNotUpdate = $this->_lowStockCollectionFactory->create()->addAttributeToFilter('disable_lowstock_update', 1)->getAllIds();
        $productCollection = $this->_lowStockCollectionFactory->create()
                                    ->addAttributeToFilter('wi_warehouse_id', $warehouse->getId());
        if (count($productsToNotUpdate) > 0)
            $productCollection->addAttributeToFilter('entity_id', ['nin' => $productsToNotUpdate]);

        foreach($productCollection as $item)
        {
            $total += $this->updateRecord($item, $warehouse->getData('w_ignore_sales_below_1'));
        }

        return $total;
    }

    public function getWarehouses()
    {
        return $this->_warehouseCollectionFactory->create()->addFieldToFilter('w_enable_lowstock_update', 1);
    }

    public function updateRecord($item, $skipCalculationBelowOne = false)
    {
        $this->_logger->log('Process sku '.$item->getSku(), 'lowstocklevel');

        $values = $this->getRecommendations($item, $skipCalculationBelowOne);
        if ($values['ideal_stock_level'] > 0 || $item->getwi_use_config_ideal_stock_level() == 0)
        {
            $warehouseItem = $this->_warehouseItemFactory->create()->load($item->getwi_id());
            $warehouseItem->setwi_use_config_ideal_stock_level(0);
            $warehouseItem->setwi_ideal_stock_level($values['ideal_stock_level']);
            $warehouseItem->setwi_use_config_warning_stock_level(0);
            $warehouseItem->setwi_warning_stock_level($values['warning_stock_level']);
            $warehouseItem->save();

            return true;
        }

        return false;
    }

    public function getRecommendations($item, $skipCalculationBelowOne = false)
    {
        $recommendations = [];
        $recommendations['supplier_lead_time'] = (int)$item->getsup_shipping_delay() + (int)$item->getsup_supply_delay();      //days
        $recommendations['optimal_stock_duration'] = $item->getw_lowstock_optimal() ? : 15;   //days
        $recommendations['sales_per_week'] = $item->getaverage_per_week();
        $recommendations['warning_stock_level_coef'] = ($item->getw_lowstock_warning_percentage() ? : 30) / 100;

        $recommendations['ideal_stock_level'] = ($recommendations['supplier_lead_time'] + $recommendations['optimal_stock_duration']) * ($recommendations['sales_per_week'] / 7);

        if ($recommendations['ideal_stock_level'] > 0 && $recommendations['ideal_stock_level'] < 1 && (!$skipCalculationBelowOne))
            $recommendations['ideal_stock_level'] = 1;
        if ($recommendations['ideal_stock_level'] > 0 && $recommendations['ideal_stock_level'] < 1 && ($skipCalculationBelowOne))
            $recommendations['ideal_stock_level'] = 0;

        $recommendations['ideal_stock_level'] = ceil($recommendations['ideal_stock_level']);

        $recommendations['warning_stock_level'] = $recommendations['ideal_stock_level'] * $recommendations['warning_stock_level_coef'];
        if ($recommendations['warning_stock_level'] > 0 && $recommendations['warning_stock_level'] < 1)
            $recommendations['warning_stock_level'] = 1;
        $recommendations['warning_stock_level'] = ceil($recommendations['warning_stock_level']);

        return $recommendations;
    }

}
