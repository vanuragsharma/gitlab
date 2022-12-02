<?php

namespace BoostMyShop\AdvancedStock\Model\Warehouse;


class Export
{
    protected $_moduleManager;
    protected $_productCollectionFactory;
    protected $_warehouseItemFactory;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Product\AllFactory $productCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\ItemFactory $warehouseItemFactory
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_warehouseItemFactory = $warehouseItemFactory;
    }

    public function getProducts($warehouseId, $date = null)
    {
        $products =  [];
        $collection = $this->getCollection($warehouseId);
        foreach($collection as $item)
        {
            $products[] = $this->getProductDetails($item, $date);
        }

        return $products;
    }

    protected function getCollection($warehouseId)
    {
        return $this->_productCollectionFactory->create()->addWarehouseFilter($warehouseId)->addRowValue();
    }

    protected function getProductDetails($item, $date)
    {
        $details = array();

        $details['product_id'] = $item['wi_product_id'];
        $details['warehouse_id'] = $item['wi_warehouse_id'];
        $details['sku'] = $this->cleanHtml($this->cleanReference($item['sku']));
        $details['product'] = $this->cleanHtml($item['name']);
        $details['cost'] = $item['cost'];
        $details['shelf_location'] = $item['wi_shelf_location'];
        if($this->supplierModuleIsInstalled()){
            $details['supplier_price'] = $item['sp_price'];
            $details['supplier_pack_qty'] = $item['sp_pack_qty'];
            $details['supplier_currency'] = $item['sup_currency'];
        }
        $details['row_total'] = $item['total_row_value'];

        if (!$date) {
            $details['qty'] = $item['wi_physical_quantity'];
            $details['qty_to_ship'] = $item['wi_quantity_to_ship'];
            $details['qty_available'] = $item['wi_available_quantity'];
        }
        else {

            $date .= ' 23:59:59';
            $details['qty'] = $this->_warehouseItemFactory->create()->calculatePhysicalQuantityFromStockMovements($item['wi_warehouse_id'], $item['wi_product_id'], $date);
            if ($item['wi_physical_quantity'] > 0)
                $details['row_total'] = $details['row_total'] / $item['wi_physical_quantity'] * $details['qty'];
            else
                $details['row_total'] = $details['qty'] * ($details['cost'] ? $details['cost'] : $details['supplier_price']);
        }

        return $details;
    }

    public function convertToCsv($products, $filePath)
    {
        $isHeader = true;

        $content = "";
        $separator = ";";
        $newLine = "\n";

        foreach($products as $product)
        {
            if ($isHeader)
            {
                $content .= implode($separator, array_keys($product)).$newLine;
                $isHeader = false;
            }

            $content .= implode($separator, $product).$newLine;
        }

        file_put_contents($filePath, $content);
    }

    public function supplierModuleIsInstalled()
    {
        return $this->_moduleManager->isEnabled('BoostMyShop_Supplier');
    }

    public function cleanReference($reference)
    {
        $t = explode('_', $reference);
        if (count($t) > 1)
            unset($t[0]);
        return implode('_', $t);
    }

    public function cleanHtml($html)
    {
        $html = html_entity_decode($html);
        return $html;
    }

}