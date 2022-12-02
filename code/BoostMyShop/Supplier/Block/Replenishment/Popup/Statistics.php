<?php

namespace BoostMyShop\Supplier\Block\Replenishment\Popup;

use Magento\Backend\Block\Template;

class Statistics extends Template
{
    protected $_supplierCollectionFactory;
    protected $_supplierFactory;
    protected $_replenishmentCollectionFactory;
    protected $_supplierProductCollectionFactory;
    protected $_storageInterface;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\CollectionFactory $supplierCollectionFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Replenishment\CollectionFactory $replenishmentCollectionFactory,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Product\CollectionFactory $supplierProductCollectionFactory,
        \Magento\Backend\Model\Auth\StorageInterface $storageInterface,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_supplierCollectionFactory = $supplierCollectionFactory;
        $this->_replenishmentCollectionFactory = $replenishmentCollectionFactory;
        $this->_supplierFactory = $supplierFactory;
        $this->_supplierProductCollectionFactory = $supplierProductCollectionFactory;
        $this->_storageInterface = $storageInterface;
    }

    public function getSupplierUrl($id)
    {
        return $this->getUrl('supplier/supplier/edit', ['_current' => true, 'sup_id' => $id]);
    }

    public function getReplenishmentCollection()
    {
        $data = [];
        $suppliers = $this->_supplierFactory->create()->getCollection();
        $replenishmentCollection = $this->_replenishmentCollectionFactory->create()->init();
        $from = $replenishmentCollection->getSelect()->getPart(\Zend_Db_Select::FROM);
        foreach ($from as $key => $join) {
            if ($join['tableName'] == 'bms_supplier_product' && $join['joinType'] == \Zend_Db_Select::LEFT_JOIN) {
                $from[$key]['joinCondition'] = '(sp_product_id = e.entity_id)';
            }
        }
        $replenishmentCollection->getSelect()->setPart(\Magento\Framework\DB\Select::FROM, $from);

        foreach ($suppliers as $supplier) {
            $collection = clone $replenishmentCollection;
            $collection->getSelect()->where('sp_sup_id ='.$supplier->getId());

            if(count($collection) != 0){
                foreach ($collection as $item) {
                    $data[$supplier->getId()][] = [
                        'sup_name' => $this->cleanJson($item->getsup_name()),
                        'qty_for_low_stock' => $item->getqty_for_low_stock(),
                        'final_price' => $item->getFinalPrice(),
                        'qty_for_backorder' => $item->getqty_for_backorder(),
                        'qty_to_order' => $item->getqty_to_order(),
                        'sup_minimum_of_order' => $item->getsup_minimum_of_order(),
                        'sup_carriage_free_amount' => $item->getsup_carriage_free_amount(),
                        'sp_price' => $item->getsp_price(),
                        'sup_currency' => $item->getsup_currency(),
                        'entity_id' => $item->getentity_id(),
                        'qty_to_receive' => $item->getqty_to_receive()
                    ];
                }
            }

        }

        return $data;
    }

    protected function cleanJson($value)
    {
        $value = str_replace("'", "", $value);
        $value = str_replace('"', "", $value);
        return $value;
    }

    public function getSupplierArray($suppliers)
    {
        $lowStockQtyAndId = $backOrderQtyAndId = $allQtyAndId = [];
        $lowStockPrice = $backOrderPrice = $totalPrice = $minOfOrder = $qtyToOrder = $carriageFreeAmount = 0;
        foreach ($suppliers as $item)
        {
            if($item['qty_for_low_stock'] > 0 && $item['qty_to_order'] > 0)
            {
                $lowStockPrice += $item['qty_for_low_stock']*$item['sp_price'];
                $lowStockQtyAndId[] = [$item['entity_id'] => $item['qty_for_low_stock']];
            }
            if ($item['qty_for_backorder'] > 0 && $item['qty_to_order'] > 0)
            {
                $backOrderPrice += $item['qty_for_backorder']*$item['sp_price'];
                $backOrderQtyAndId[] = [$item['entity_id'] => $item['qty_for_backorder']];
            }
            $carriageFreeAmount = $item['sup_carriage_free_amount'];
            $minOfOrder = $item['sup_minimum_of_order'];
            $qtyToOrder += $item['qty_to_order'];
            if($item['qty_to_order'] > 0)
                $allQtyAndId[] = [$item['entity_id'] => $item['qty_to_order']];
            $supName = $item['sup_name'];
            $supCurrency = $item['sup_currency'];
        }

        $data  =     [
            'sup_name' => $supName,
            'low_stock_price' =>$lowStockPrice,
            'low_stock' =>$lowStockQtyAndId,
            'back_order_price' =>$backOrderPrice,
            'back_order' =>$backOrderQtyAndId,
            'carriage_free_amount' =>$carriageFreeAmount,
            'min_of_order' =>$minOfOrder,
            'qty_to_order' => $qtyToOrder,
            'total_price' => $lowStockPrice+$backOrderPrice,
            'all' => $allQtyAndId,
            'sup_currency' => $supCurrency
        ];

        return $data;
    }

    public function getPoOption($key, $lowStock, $backOrder, $all)
    {
        $html = "<select id='purchaseorder'>";
        $html .= "<option selected='selected' value='Choose one'>".__('Choose one')."</option>";
        if($lowStock)
            $html .='<option value="'. $key.'&low_stock' .'" >'. __('Create PO for low stock products') .'</option>';
        if($backOrder)
            $html .='<option value="'. $key.'&back_order' .'" >'. __('Create PO for backorder products') .'</option>';
        if($all)
            $html .='<option value="'. $key.'&all' .'" >'. __('Create PO for all products') .'</option>';

        $html .= '</select>';

        return $html;
    }
}
