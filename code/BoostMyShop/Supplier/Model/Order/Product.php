<?php

namespace BoostMyShop\Supplier\Model\Order;


class Product extends \Magento\Framework\Model\AbstractModel
{
    protected $_product = null;
    protected $_order = null;

    protected $_productFactory = null;
    protected $_orderFactory = null;
    protected $_productHelper = null;
    protected $_supplierProdeuct;
    protected $_config;
    protected $_dateTime;

    protected $_productSupplierAssociation = null;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\ResourceModel\Order\Product');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \BoostMyShop\Supplier\Model\Order $orderFactory,
        \BoostMyShop\Supplier\Model\Supplier\Product $_supplierProdeuct,
        \BoostMyShop\Supplier\Model\Product $productHelper,
        \BoostMyShop\Supplier\Model\Config $config,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_productFactory = $productFactory;
        $this->_orderFactory = $orderFactory;
        $this->_supplierProdeuct = $_supplierProdeuct;
        $this->_productHelper = $productHelper;
        $this->_config = $config;
        $this->_dateTime = $dateTime;
    }



    public function getProduct()
    {
        if ($this->_product == null)
        {
            $this->_product = $this->_productFactory->create()->load($this->getpop_product_id());
        }
        return $this->_product;
    }

    public function getOrder()
    {
        if ($this->_order == null)
        {
            $this->_order = $this->_orderFactory->load($this->getpop_po_id());
        }
        return $this->_order;
    }

    public function getPendingQty()
    {
        $value = (int)$this->getPopQty() - (int)$this->getPopQtyReceived();
        if ($value < 0)
            $value = 0;
        return $value;
    }

    public function getProductSupplierAssociation(){
        if (!$this->_productSupplierAssociation)
        {
            $sup_id = $this->getOrder()->getpo_sup_id();
            if($this->getpop_product_id() && $sup_id){
                $this->_productSupplierAssociation = $this->_supplierProdeuct->loadByProductSupplier($this->getpop_product_id(),$sup_id);
            }
        }
        return $this->_productSupplierAssociation;
    }

    public function getMoq(){
        if($this->getProductSupplierAssociation()->getsp_moq())
            return (int)$this->getProductSupplierAssociation()->getsp_moq();
        else 
            return 0;
    }

    public function hasMoqIssue(){
        if($this->_config->getSetting('general/pack_quantity')){
            if($this->getOrderedQtyUnit() < $this->getMoq()){
                return false;
            }
        } else {
            if((int)$this->getpop_qty() < $this->getMoq()){
                return false;
            }
        }
        return true;
    }

    public function getOrderedQtyUnit(){
        return (int)$this->getpop_qty() * (int)$this->getpop_qty_pack();
    }

    public function getPendingQtyUnit(){
        return $this->getPendingQty() * (int)$this->getpop_qty_pack();
    }

    public function getUnitPrice(){
        if ($this->getpop_qty_pack() > 0)
            return $this->calculateWithDiscount((float)$this->getpop_price() / (int)$this->getpop_qty_pack());
        else
            return 0;
    }

    public function getUnitPriceBase(){
        if ($this->getpop_qty_pack() > 0)
            return $this->calculateWithDiscount((float)$this->getpop_price_base() / (int)$this->getpop_qty_pack());
        else
            return 0;
    }

    /**
     * Return real final cost for the product, including discounts and all...
     *
     * @return string
     */
    public function getUnitPriceWithCost(){
        $value = $this->getUnitPrice();
        if ($this->getOrder()->getpo_global_discount())
        {
            $discounted = $value / 100 * (float)$this->getOrder()->getpo_global_discount();
            $value -= $discounted;
        }
        $value += (float)$this->getpop_extended_cost();
        return $value;
    }

    /**
     * Return real final cost for the product, including discounts and all...
     *
     * @return string
     */
    public function getUnitPriceBaseWithCost(){
        $value = $this->getUnitPriceBase() + (float)$this->getpop_extended_cost_base();
        if ($this->getOrder()->getpo_global_discount())
        {
            $discounted = $value / 100 * (float)$this->getOrder()->getpo_global_discount();
            $value -= $discounted;
        }
        return $value;
    }

    public function getSubTotal()
    {
        $subTotal = ((int)$this->getpop_qty() * (float)$this->getpop_price());
        return $this->calculateWithDiscount($subTotal);
    }

    public function getSubTotalBase()
    {
        $subTotal = ((int)$this->getpop_qty() * (float)$this->getpop_price_base());
        return $this->calculateWithDiscount($subTotal);
    }

    public function getGrandTotalWithCostsAndDiscount()
    {
        return ($this->getpop_qty() * $this->getUnitPriceWithCost());
    }

    protected function calculateWithDiscount($value)
    {
        $value = $value - ($value / 100 * (float)$this->getpop_discount_percent());
        return number_format($value, 4, '.', '');
    }

    public function beforeSave()
    {
        if (!$this->getId())
        {
            $this->setpop_sku($this->getProduct()->getSku());
            $this->setpop_name($this->getProduct()->getName());
            $this->setpop_created_at($this->_dateTime->gmtDate());
        }


        $this->setpop_updated_at($this->_dateTime->gmtDate());

        //update total information
        //todo : use pop_change_rate to avoid to load PO
        $this->setpop_price_base((float)$this->getpop_price() * $this->getOrder()->getpo_change_rate());
        $this->setpop_extended_cost_base((float)$this->getpop_extended_cost() * (float)$this->getpop_change_rate());
        $this->setpop_subtotal($this->getSubTotal());
        $this->setpop_subtotal_base($this->getSubTotalBase());

        $this->setpop_tax((float)$this->getpop_subtotal() / 100 * (float)$this->getpop_tax_rate());
        $this->setpop_tax_base((float)$this->getpop_subtotal_base() / 100 * (float)$this->getpop_tax_rate());

        $this->setpop_grandtotal((float)$this->getpop_subtotal() + (float)$this->getpop_tax());
        $this->setpop_grandtotal_base((float)$this->getpop_subtotal_base() + (float)$this->getpop_tax_base());
    }

    public function afterSave()
    {
        //update supplier SKU in product / supplier association
        if (($this->getData('pop_supplier_sku')) && ($this->getData('pop_supplier_sku') != $this->getOrigData('pop_supplier_sku')))
        {
            if ($this->getProductSupplierAssociation())
                $this->getProductSupplierAssociation()->setsp_sku($this->getData('pop_supplier_sku'))->save();
        }

        if ($this->getpop_qty_received() && ($this->getpop_price() > 0) &&
            (
                $this->fieldValueHasChanged('pop_price')
                || $this->fieldValueHasChanged('pop_price_base')
                || $this->fieldValueHasChanged('pop_discount_percent')
                || $this->fieldValueHasChanged('pop_qty_pack')
                || $this->fieldValueHasChanged('pop_qty_received')
                || $this->fieldValueHasChanged('pop_extended_cost')
            )
        )
        {
            if ($this->getProductSupplierAssociation())
                $this->getProductSupplierAssociation()->updateLastBuyingPrice();
        }
    }

    public function afterDelete()
    {
        $this->getOrder()->addHistory(__('Product %1 removed', $this->getOrder()->getProductSku($this->getProduct()->getSku())));
        $this->_productHelper->updateQuantityToReceive($this->getpop_product_id());
    }

    public function fieldValueHasChanged($key)
    {
        return ($this->getData($key) != $this->getOrigData($key));
    }
}
