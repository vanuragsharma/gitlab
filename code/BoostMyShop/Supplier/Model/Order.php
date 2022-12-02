<?php

namespace BoostMyShop\Supplier\Model;


/**
 * Class Order
 * @package BoostMyShop\Supplier\Model
 */
class Order extends \Magento\Framework\Model\AbstractModel
{
    protected $_dateTime = null;

    protected $_orderProductCollectionFactory = null;
    protected $_productSupplierFactory = null;
    protected $_productSupplierResourceFactory = null;
    protected $_orderProductFactory = null;
    protected $_receptionFactory = null;
    protected $_historyFactory = null;
    protected $authSession;
    protected $_storeManager;
    protected $_currencyFactory;
    protected $_productFactory;
    protected $_supplier;
    protected $_supplierFactory;
    protected $_invOrderCollectionFactory = null;
    protected $_manager;
    protected $_userFactory;
    protected $_config;
    protected $_currency;
    protected $_logger;
    protected $_supplierProdeuct;
    protected $_websiteCollectionFactory;

    protected $_eventPrefix = 'bms_supplier_order';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\ResourceModel\Order');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $orderProductCollectionFactory,
        \BoostMyShop\Supplier\Model\Order\ProductFactory $orderProductFactory,
        \BoostMyShop\Supplier\Model\Order\ReceptionFactory $receptionFactory,
        \BoostMyShop\Supplier\Model\Order\HistoryFactory $historyFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \BoostMyShop\Supplier\Model\Supplier\Product $_supplierProdeuct,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\ProductFactory $productSupplierResourceFactory,
        \BoostMyShop\Supplier\Model\Supplier\ProductFactory $productSupplierFactory,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory,
        \BoostMyShop\Supplier\Model\Product $productFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Invoice\Order\CollectionFactory $invOrderCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \BoostMyShop\Supplier\Model\Config $config,
        \Magento\User\Model\User $userFactory,
        \BoostMyShop\Supplier\Helper\Logger $logger,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_dateTime = $dateTime;
        $this->_localeResolver = $localeResolver;
        $this->_timezone = $timezone;
        $this->_storeManager = $storeManager;
        $this->_orderProductCollectionFactory = $orderProductCollectionFactory;
        $this->_orderProductFactory = $orderProductFactory;
        $this->_supplierFactory = $supplierFactory;
        $this->_receptionFactory = $receptionFactory;
        $this->_historyFactory = $historyFactory;
        $this->_currencyFactory = $currencyFactory;
        $this->_supplierProdeuct = $_supplierProdeuct;
        $this->authSession = $authSession;
        $this->_userFactory = $userFactory;
        $this->_productSupplierFactory = $productSupplierFactory;
        $this->_productSupplierResourceFactory = $productSupplierResourceFactory;
        $this->_invOrderCollectionFactory = $invOrderCollectionFactory;
        $this->_config = $config;
        $this->_productFactory = $productFactory;
        $this->_logger = $logger;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
    }

    public function beforeSave()
    {
        if (!$this->getId())
            $this->setpo_created_at($this->_dateTime->gmtDate());
        
        if (!$this->getpo_shipping_cost())
		$this->setpo_shipping_cost(0);

        if (!$this->getpo_change_rate())
            $this->setpo_change_rate(1);

        if (!$this->getpo_additionnal_cost())
            $this->setpo_additionnal_cost(0);

        if (!$this->getpo_tax_rate())
            $this->setpo_tax_rate(0);

        $this->setpo_updated_at($this->_dateTime->gmtDate());

        $this->setpo_shipping_cost_base($this->getpo_shipping_cost() * $this->getpo_change_rate());
        $this->setpo_additionnal_cost_base($this->getpo_additionnal_cost() * $this->getpo_change_rate());
    }

    public function afterSave()
    {
        parent::afterSave();

        $this->_getResource()->copyChangeRateToProducts($this->getId(), $this->getpo_change_rate());
        $this->updateDeliveryProgress();
        $this->updateTotals();
    }


    public function beforeDelete()
    {
        parent::beforeDelete();

        foreach($this->getAllItems() as $item)
            $item->delete();

        return $this;
    }

    /**
     * Apply default values for fields
     *
     * @return $this
     */
    public function applyDefaultData($supplierId)
    {
        if ($this->authSession->isLoggedIn()) {
            $currentUserId = $this->authSession->getUser()->getId();
            $this->setpo_manager($currentUserId);
        }
        $this->setpo_status(\BoostMyShop\Supplier\Model\Order\Status::draft);
        $this->setpo_reference($this->_getResource()->getNextReference($this->getPoType(), $this->getDefaultWebsiteId()));
        $this->setpo_change_rate(1);
        $this->setpo_shipping_cost(0);
        $this->setpo_additionnal_cost(0);

        $this->setpo_delivery_progress(0);

        $this->setpo_sup_id($supplierId);
            $this->setpo_tax_rate($this->getSupplier()->getsup_tax_rate()?$this->getSupplier()->getsup_tax_rate():0);
        $this->setpo_currency($this->getSupplier()->getsup_currency());
        $this->setpo_global_discount($this->getSupplier()->getsup_global_discount());

        $eta = $this->_dateTime->gmtDate();
        $eta = date('Y-m-d', strtotime($eta. ' + '.$this->getSupplier()->getLeadTime().' days'));
        $this->setpo_eta($eta);

        return $this;
    }

    public function getDefaultWebsiteId()
    {
        return $this->_websiteCollectionFactory->create()->getFirstItem()->getId();
    }

    /**
     *
     */
    public function getAllItems()
    {
        $collection = $this->_orderProductCollectionFactory->create();
        $collection->addOrderFilter($this->getId());
        return $collection;
    }

    public function getItemByProductId($productId)
    {
        foreach($this->getAllItems() as $item)
        {
            if ($item->getpop_product_id() == $productId)
                return $item;
        }
        return false;
    }

    public function getStore()
    {
        $storeId = ($this->getpo_store_id() ? $this->getpo_store_id() : 1);
        return $this->_storeManager->getStore($storeId);
    }

    public function getSupplier()
    {
        if (!$this->_supplier)
            $this->_supplier = $this->_supplierFactory->create()->load($this->getpo_sup_id());
        return $this->_supplier;
    }

    public function getManager()
    {
        if (!$this->_manager)
        {
            if ($this->getpo_manager() > 0)
                $this->_manager = $this->_userFactory->load($this->getpo_manager());
            else
                $this->_manager = $this->_userFactory;
        }
        return $this->_manager;
    }

    public function addProduct($productId, $qty, $additionnal = [], $forcePriceUpdate = false)
    {
        $obj = $this->_orderProductFactory->create();
        $obj->setpop_po_id($this->getId());
        $obj->setpop_product_id($productId);
        $obj->setpop_qty($qty);
        $obj->setpop_qty_received(0);
        $obj->setpop_tax_rate($this->getpo_tax_rate());
        $obj->setpop_change_rate($this->getpo_change_rate());

        $id = $this->_productSupplierResourceFactory->create()->getIdFromProductSupplier($productId, $this->getpo_sup_id());
        if ($id)
        {
            $productSupplierData = $this->_productSupplierFactory->create()->load($id);
            $obj->setpop_supplier_sku($productSupplierData->getsp_sku());

            if ($this->_config->getSetting('order_product/default_price') == 'product_supplier_association') {
                $obj->setpop_price($productSupplierData->getsp_price());
                $obj->setpop_price_base($productSupplierData->getsp_base_price());
            }
        }
        else
        {
            //create product / supplier association
            $this->_productSupplierFactory->create()->associate($productId, $this->getpo_sup_id());
        }

        //import buying price from product cost
        if ($this->_config->getSetting('order_product/default_price') == 'product_cost') {
            $cost = $this->_productFactory->getCost($productId);
            $obj->setpop_price($cost);
            $obj->setpop_price_base($cost);
        }

        //update buying price from PO Add Products tab
        if ($forcePriceUpdate == true) {
            if (array_key_exists('buying_price', $additionnal)
                && $additionnal['buying_price'] != ''
                && $additionnal['buying_price'] != 'undefined'
                && is_numeric($additionnal['buying_price'])
            ) {
                $obj->setpop_price($additionnal['buying_price']);
                $obj->setpop_price_base($additionnal['buying_price']);
            }
        }

        if ($this->_config->getSetting('general/pack_quantity')){
            $supplyNeeds = true;
            $pack_qty = '';
            if(array_key_exists('qty_pack', $additionnal))
            {
                $supplyNeeds = false;
                $pack_qty = $additionnal['qty_pack'];
            } 

            if(empty($pack_qty))
            {
                $supplier = $this->getSupplier();
                if ($supplier->isAssociatedToProduct($productId)){
                    $productSupplier = $this->_productSupplierFactory->create()->loadByProductSupplier($productId, $this->getpo_sup_id());
                    
                    if($productSupplier->getsp_pack_qty() > 0){
                        $pack_qty = $productSupplier->getsp_pack_qty();
                    } else {
                        $pack_qty = 1;
                    }
                } else {
                    $pack_qty = 1;
                }
            } 

            if($supplyNeeds){
                $qty = ceil($qty / $pack_qty);
                $obj->setpop_qty($qty);
            }
            $additionnal['pop_qty_pack'] = $pack_qty;
        }

        foreach($additionnal as $k => $v)
        {
            if ($v)
                $obj->setData($k, $v);
        }

        $this->addHistory(__('Product %1 added', $this->getProductSku($obj->getProduct()->getSku())));

        $obj->save();

        return $this;
    }

    public function getProductSku($sku)
    {
        return $sku;
    }

    /**
     * @param $userName
     * @param $products
     */
    public function processReception($userName, $products)
    {
        $obj = $this->_receptionFactory->create();
        $obj->init($userName, $this);
        $obj->save();

        $obj->addProducts($products);

        $this->updateQtyToReceive();

        if ($this->_config->updateProductCostAfterReception())
            $this->updateProductCosts();

        $this->_eventManager->dispatch('bms_supplier_order_after_reception_processed', ['object' => $this]);

        return $obj;
    }

    public function updateDeliveryProgress()
    {
        $this->_getResource()->updateDeliveryProgress($this->getId());
    }

    public function updateTotals()
    {
        $this->_getResource()->updateTotals($this);
    }

    public function updateQtyToReceive()
    {
        foreach($this->getAllItems() as $item)
        {
            $this->_productFactory->updateQuantityToReceive($item->getpop_product_id());
        }
    }

    public function getCurrency()
    {
        if (!$this->_currency)
            $this->_currency = $this->_currencyFactory->create()->load($this->getpo_currency());
        return $this->_currency;
    }

    public function reachesMinimumOfOrder()
    {
        if ($this->getSupplier()->getsup_minimum_of_order() > 0)
            return ($this->getSupplier()->getsup_minimum_of_order() < $this->getpo_grandtotal());
        return true;
    }

    public function reachesCarriageFree()
    {
        if ($this->getSupplier()->getsup_carriage_free_amount() > 0)
            return ($this->getSupplier()->getsup_carriage_free_amount() < $this->getpo_grandtotal());
        return true;
    }

    public function updateExtendedCosts()
    {
        $totalCosts = $this->getpo_shipping_cost() + $this->getpo_additionnal_cost();
        $unitCost = 0;

        //calculate extended costs
        $method = $this->_config->getExtendedCostMethod();
        switch($method)
        {
            case 'quantity':
                $totalItems = $this->getTotalItems();
                if ($totalItems > 0)
                    $unitCost = $totalCosts / $totalItems;
                break;
            case 'value':
                $totalValue = $this->getTotalItemsValue();
                if ($totalValue > 0)
                    $unitCost = $totalCosts / $totalValue;
                break;
            case 'weight':
                $totalWeight = $this->getTotalOrderWeight();
                if ($totalWeight > 0)
                    $unitCost = $totalCosts / ($totalWeight * 100);   //unit cost is calculated for 0.01 weight
                break;
        }
        $this->_logger->log('Update extended costs with method '.$method.' and value : '.$unitCost);
        //copy to products
        $this->_getResource()->updateExtendedCostForItems($this->getId(), $method, $unitCost, $this->getpo_change_rate());
    }

    public function getTotalItems()
    {
        $qty = 0;
        foreach($this->getAllItems() as $item)
            $qty += $item->getpop_qty();
        return $qty;
    }

    public function getTotalItemsValue()
    {
        $value = 0;
        foreach($this->getAllItems() as $item)
            $value += $item->getSubTotal();
        return $value;
    }

    public function getTotalOrderWeight()
    {
        $weight = 0;
        foreach($this->getAllItems() as $item){
            $weight += $item->getpop_qty()*$this->_productFactory->getAttributeValue($item->getpop_product_id(),'weight');
        }
        return number_format($weight,2);
    }

    /**
     * Update product cost attribute
     */
    public function updateProductCosts()
    {
        $this->_logger->log('Update product costs for PO #'.$this->getId());
        foreach($this->getAllItems() as $item)
        {
            if (($item->getpop_qty_received() > 0) && ($item->getpop_price_base() > 0))
            {
                $this->_productFactory->updateCost($item->getpop_product_id());
            }
        }
    }

    public function getToken()
    {
        return sha1($this->getId().' po '.$this->getpo_created_at());
    }


    public function getInvoices()
    {
        $collection = $this->_invOrderCollectionFactory->create();
        $collection->addOrderFilter($this->getId());        
        return $collection;
    }

    public function getTotalInvoiceApplied()
    {
        $collection = $this->_invOrderCollectionFactory->create()->addFieldToFilter('bsio_order_id', $this->getId());

        $collection->getSelect()->columns(['bsio_total' => new \Zend_Db_Expr('SUM(bsio_total)')])->group('bsio_order_id');

        return $collection->getFirstItem()->getBsioTotal();
    }

    public function getTotalInvoiceToApply()
    {
        return $this->getGrandtotal() - $this->getTotalInvoiceApplied();
    }

    public function getGlobalDiscountAmount($subTotal = null)
    {
        $subTotal = ($subTotal ? $subTotal : $this->getpo_subtotal());
        $value = $subTotal / 100 * $this->getpo_global_discount();

        return number_format($value, 4, '.', '');
    }

    public function getGlobalDiscountBaseAmount($subTotalBase = null)
    {
        $subTotalBase = ($subTotalBase ? $subTotalBase : $this->getpo_subtotal());
        $value = $subTotalBase / 100 * $this->getpo_global_discount();
        return number_format($value, 4, '.', '');
    }

    public function hasMoqIssue(){
        foreach($this->getAllItems() as $item)
        {
            if(!$item->hasMoqIssue()){
                return false;
            }
        }
        return true;
    }

    public function updateMissingPrices()
    {
        $result = $this->_getResource()->updateMissingPrices($this->getId());
        $this->setpo_missing_price($result);
        return $this;
    }

    public function getBillingAddress()
    {
        return $this->_config->getSetting('pdf/billing_address', $this->getpo_store_id());
    }

    public function getShippingAddress()
    {
        return $this->_config->getSetting('pdf/shipping_address', $this->getpo_store_id());
    }

    public function convertToBaseCurrency($value)
    {
        return ($value * $this->getpo_change_rate());
    }

    public function formatDate($dateString)
    {
        return $this->_timezone->formatDateTime(
            new \DateTime($dateString),
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            $this->_localeResolver->getDefaultLocale(),
            $this->_timezone->getConfigTimezone('store', (int)$this->getpo_store_id())
        );
    }

    public function getShippingLabelUrl()
    {
        $url = false;

        if ($this->getpo_shipping_label_path())
            $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'purchase_order/shipping_label'.$this->getpo_shipping_label_path();

        return $url;
    }

    /**
     *  add history
     * @param $description
     */
    public function addHistory($description)
    {
        if ($this->authSession->isLoggedIn())
        {
            $userName = $this->authSession->getUser()->getUsername();
        }else
            {
                $userName ="system";
            }

        $obj = $this->_historyFactory->create();
        $obj->init($userName,$description, $this);
        $obj->save();
    }

}
