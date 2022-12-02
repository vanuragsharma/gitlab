<?php
namespace BoostMyShop\AdvancedStock\Block\Product\Edit\Tab;

class Stocks extends AbstractTab
{
    protected $_template = 'Product/Edit/Tab/Stocks.phtml';

    public function getStocks()
    {
        return $this->_warehouseItemCollectionFactory->create()->addProductFilter($this->getProduct()->getId())->joinWarehouse();
    }

    public function getDefaultWarningStockLevel($warehouseId)
    {
        $result = $this->_warehouseCollectionFactory->create()->addFieldToFilter('w_id', $warehouseId)->getFirstItem();
        return $result->getw_default_warning_stock_level();
    }

    public function getDefaulIdealStockLevel($warehouseId)
    {
        $result = $this->_warehouseCollectionFactory->create()->addFieldToFilter('w_id', $warehouseId)->getFirstItem();
        return $result->getw_default_ideal_stock_level();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('advancedstock/product/saveWarehouseItem');
    }

}