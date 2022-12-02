<?php

namespace BoostMyShop\OrderPreparation\Model;

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

    public function getParamValue($path, $websiteId)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function isBatchEnable()
    {
        return $this->_scopeConfig->getValue('orderpreparation/batch/enable');
    }

    public function maxOrdersCountInUniqueBatch()
    {
        return $this->getSetting('batch/max_order_count_in_batch_unique');
    }

    public function maxOrdersCountInSingleBatch()
    {
        return $this->getSetting('batch/max_order_count_in_batch_single');
    }

    public function maxOrdersCountInMultipleBatch()
    {
        return $this->getSetting('batch/max_order_count_in_batch_multiple');
    }

    public function maxOrdersCountInAllBatch()
    {
        return $this->getSetting('batch/max_order_count_in_batch_all');
    }

    public function getBatchDisableLabelPregeneration()
    {
        return $this->getSetting('batch/disable_label_pregeneration');
    }

    public function getUniqueProductMinimumOrderCount()
    {
        return $this->_scopeConfig->getValue('orderpreparation/batch/unique_product_minimum_order_count');
    }

    public function getBatchPdfFormat()
    {
        return $this->_scopeConfig->getValue('orderpreparation/batch/print_format');
    }

    public function getOrderStateComplete(){
        return $this->_scopeConfig->getValue('orderpreparation/packing/order_state_complete');
    }

    public function getOrderStateProcessing(){
        return $this->_scopeConfig->getValue('orderpreparation/packing/order_state_processing');
    }

    public function getChangeOrderStatusAfterPacking()
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/change_order_status_after_packing');
    }

    public function getSetting($path, $storeId = 0)
    {
        return $this->_scopeConfig->getValue('orderpreparation/'.$path, 'store', $storeId);
    }

    public function getGlobalSetting($path, $storeId = 0)
    {
        return $this->_scopeConfig->getValue($path, 'store', $storeId);
    }

    public function getBarcodeAttribute()
    {
        return $this->_scopeConfig->getValue('orderpreparation/attributes/barcode_attribute');
    }

    public function getMpnAttribute()
    {
        return $this->_scopeConfig->getValue('orderpreparation/attributes/mpn_attribute');
    }

    public function getVolumeAttribute()
    {
        return $this->_scopeConfig->getValue('orderpreparation/attributes/volume_attribute');
    }

    public function getPackageNumberAttribute()
    {
        return $this->_scopeConfig->getValue('orderpreparation/attributes/package_number_attribute');
    }

    public function getLocationAttribute()
    {
        return $this->_scopeConfig->getValue('orderpreparation/attributes/shelflocation_attribute');
    }

    public function getOrderStatusesForTab($tab)
    {
        $statuses = explode(',', $this->_scopeConfig->getValue('orderpreparation/status_mapping/'.$tab));
        return $statuses;
    }

    public function getAllowPartialPacking()
    {
        return $this->getSetting('packing/allow_partial');
    }

    public function getCreateInvoice()
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/create_invoice');
    }

    public function getCreateShipment()
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/create_shipment');
    }

    public function includeInvoiceInDownloadDocuments()
    {
        return $this->_scopeConfig->getValue('orderpreparation/download/invoice');
    }

    public function includeShipmentInDownloadDocuments()
    {
        return $this->_scopeConfig->getValue('orderpreparation/download/shipment');
    }

    public function getPdfPickingLayout()
    {
        return $this->_scopeConfig->getValue('orderpreparation/picking/pdf_layout');
    }

    public function isOrderEditorEnabled()
    {
        return $this->_scopeConfig->getValue('orderpreparation/order_editor/enabled');
    }

    public function isErpIsInstalled()
    {
        return $this->_moduleManager->isEnabled('BoostMyShop_Erp');
    }

    public function displayCustomOptionsOnPicking()
    {
        return $this->_scopeConfig->getValue('orderpreparation/picking/display_options');
    }

    public function pickingListOnePagePerOrder()
    {
        return $this->_scopeConfig->getValue('orderpreparation/picking/one_page_per_order');
    }

    public function includeGlobalPickingList()
    {
        return $this->_scopeConfig->getValue('orderpreparation/picking/include_global_pickinglist');
    }

    public function getGroupBundleItems()
    {
        return $this->_scopeConfig->getValue('orderpreparation/picking/group_bundle_items');
    }

    public function canEditShippingMethod()
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/can_edit_shipping_method');
    }

    public function createInvoiceWhenAddedToInProgress()
    {
        return $this->_scopeConfig->getValue('orderpreparation/inprogress/create_invoice');
    }

    public function getPackOrderByProducBarcode()
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/pack_order_by_product_barcode', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    public function getFlushPackedOrders()
    {
        return $this->_scopeConfig->getValue('orderpreparation/flush_orders/flush_packed_orders');
    }

    public function getRemoveOrderWhenShipped($storeId)
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/remove_order_when_shipped', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getLargeOrderMode($websiteId)
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/large_order_mode', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }
    public function isOrganizerModuleInstall()
    {
       return  $this->_moduleManager->isEnabled('BoostMyShop_Organizer');
    }

    public function isAdvancedstockModuleInstall()
    {
        return  $this->_moduleManager->isEnabled('BoostMyShop_AdvancedStock');
    }

    public function getPickingPerBins($storeId = 0)
    {
        return $this->getSetting('picking/picking_per_bins', $storeId);
    }

    public function getCartBinSize($storeId = 0)
    {
        return $this->getSetting('picking/cart_bin_size', $storeId);
    }

    public function getAutoCommit($websiteId)
    {
        return $this->_scopeConfig->getValue('orderpreparation/packing/auto_commit', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getManyOrdersMode($storeId = 0)
    {
        return $this->getSetting('general/many_orders_mode', $storeId);
    }
    public function getPickingPrintFormat()
    {
        return $this->_scopeConfig->getValue('orderpreparation/picking/print_format');
    }

}