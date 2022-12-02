<?php

namespace BoostMyShop\AdvancedStock\Model;

class Config
{

    protected $_scopeConfig;
    protected $_moduleManager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->_scopeConfig = $scopeConfig;
        $this->_moduleManager = $moduleManager;
    }

    public function getSetting($path, $storeId = 0)
    {
        return $this->_scopeConfig->getValue('advancedstock/'.$path, 'store', $storeId);
    }

    public function getPendingOrderStatuses()
    {
        return explode(',', $this->_scopeConfig->getValue('advancedstock/opened_orders/opened_orders_statuses'));
    }

    public function getBarcodeAttribute()
    {
        return $this->_scopeConfig->getValue('advancedstock/attributes/barcode_attribute');
    }

    public function getManufacturerAttribute()
    {
        return $this->_scopeConfig->getValue('advancedstock/attributes/manufacturer_attribute');
    }

    public function getSalesHistoryQtySelection()
    {
        return $this->_scopeConfig->getValue('advancedstock/stock_level/history_qty_selection');
    }

    public function getSalesHistoryRanges()
    {
        return explode(',', $this->_scopeConfig->getValue('advancedstock/stock_level/history_ranges'));
    }

    public function displayStocksOnFrontEnd()
    {
        return $this->_scopeConfig->getValue('advancedstock/frontend/display_stocks');
    }

    public function getDecreaseStockWhenOrderIsPlaced()
    {
        return $this->_scopeConfig->getValue('cataloginventory/options/can_subtract');
    }

    public function canBackInStock()
    {
        return $this->_scopeConfig->getValue('cataloginventory/options/can_back_in_stock');
    }

    public function isErpIsInstalled()
    {
        return $this->_moduleManager->isEnabled('BoostMyShop_Erp');
    }

    public function isSupplierIsInstalled()
    {
        return $this->_moduleManager->isEnabled('BoostMyShop_Supplier');
    }

    public function getMagentoBackorderSetting()
    {
        return $this->_scopeConfig->getValue('cataloginventory/item_options/backorders');
    }

    public function displayAdvancedLog()
    {
        return $this->_scopeConfig->getValue('advancedstock/general/store_sm_stacktrace');
    }

}
