<?php

namespace BoostMyShop\AdvancedStock\Model\Warehouse;


class ProductsImportHandler
{

    protected $csvProcessor;
    protected $_product;
    protected $fieldsIndexes = [];
    protected $_warehouseId;
    protected $_warehouseItemFactory;
    protected $_stockMovementFactory;
    protected $_productFactory;

    protected $_results = [];
    protected $_errors = [];

    protected $_barcodeAttribute;

    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->_product = $product;
        $this->_productFactory = $productFactory;
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_stockMovementFactory = $stockMovementFactory;
        $this->_config = $config;
    }

    public function importFromCsvFile($warehouseId, $filePath, $separator = ";")
    {
        $this->_warehouseId = $warehouseId;
        $this->_barcodeAttribute = $this->_config->getBarcodeAttribute();

        if (!($filePath)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }

        //perform checks
        $this->csvProcessor->setDelimiter($separator);
        $rows = $this->csvProcessor->getData($filePath);
        if (!isset($rows[0]))
            throw new \Exception('The file is empty');

        $this->checkColumns($rows[0]);

        //import rows
        $count = 0;
        foreach ($rows as $rowIndex => $rowData) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }

            if ($this->_importRow($rowData))
                $count++;
        }

        return $count;
    }



    protected function _importRow($rowData)
    {
        $sku = (isset($this->fieldsIndexes['sku']) ? $rowData[$this->fieldsIndexes['sku']] : '');
        $qty = (isset($this->fieldsIndexes['qty']) ? $rowData[$this->fieldsIndexes['qty']] : '');
        $barcode = (isset($this->fieldsIndexes['barcode']) ? $rowData[$this->fieldsIndexes['barcode']] : '');
        $shelfLocation = (isset($this->fieldsIndexes['shelf_location']) ? $rowData[$this->fieldsIndexes['shelf_location']] : '');
        $warningStockLevel = (isset($this->fieldsIndexes['warning_stock_level']) ? $rowData[$this->fieldsIndexes['warning_stock_level']] : '');
        $useConfigWarningStockLevel = (isset($this->fieldsIndexes['use_config_warning_stock_level']) ? $rowData[$this->fieldsIndexes['use_config_warning_stock_level']] : '');
        $idealStockLevel = (isset($this->fieldsIndexes['ideal_stock_level']) ? $rowData[$this->fieldsIndexes['ideal_stock_level']] : '');
        $useConfigIdealStockLevel = (isset($this->fieldsIndexes['use_config_ideal_stock_level']) ? $rowData[$this->fieldsIndexes['use_config_ideal_stock_level']] : '');

        if (!$sku)
        {
            $this->_errors[] = 'Empty sku';
            return false;
        }

        //find product (by sku OR barcode
        $productId = $this->getProductId($sku, $barcode);
        if (!$productId)
        {
            $this->_errors[] = 'Unknown sku : '.$sku.' / barcode '.$barcode;
            return false;
        }

        //check qty
        if($qty != '' && !ctype_digit($qty)){
            $this->_results[] = 'qty : '.$qty.' for sku : '.$sku.' is not a valid integer';
            return false;
        }
        
        $stockItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $this->_warehouseId);

        if ($shelfLocation != '')
            $stockItem->setwi_shelf_location($shelfLocation);

        if ($warningStockLevel != '')
            $stockItem->setwi_warning_stock_level($warningStockLevel);

        if ($useConfigWarningStockLevel != '')
            $stockItem->setwi_use_config_warning_stock_level($useConfigWarningStockLevel);

        if ($idealStockLevel != '')
            $stockItem->setwi_ideal_stock_level($idealStockLevel);

        if ($useConfigIdealStockLevel != '')
            $stockItem->setwi_use_config_ideal_stock_level($useConfigIdealStockLevel);

        $stockItem->save();

        //manage quantity via stock movement
        if (($qty != '') && ($qty >= 0))
        {
            $userId = null;

            $this->_stockMovementFactory->create()->updateProductQuantity($stockItem->getwi_product_id(),
                $stockItem->getwi_warehouse_id(),
                $stockItem->getwi_physical_quantity(),
                $qty,
                'From warehouse import',
                $userId);

            $this->_results[] = 'Stock updated for sku '.$sku.' to '.$qty;
        }
            
        return true;
    }

    public function getProductId($sku, $barcode)
    {
        $productId = false;
        if ($sku)
            $productId = $this->_product->getIdBySku($sku);
        if (!$productId) {
            if ($barcode && $this->_barcodeAttribute)
            {
                $product = $this->_productFactory->create()->loadByAttribute($this->_barcodeAttribute, $barcode);
                if (!$product)
                {
                    return false;
                }
                else
                    $productId = $product->getId();
            }
            else
            {
                return false;
            }
        }
        return $productId;
    }


    public function checkColumns($columns)
    {
        $mandatory = [
        ];

        for($i=0;$i<count($columns);$i++)
        {
            $cleanColumn = preg_replace("/\xEF\xBB\xBF/", "", $columns[$i]);
            $this->fieldsIndexes[$cleanColumn] = $i;
        }

        foreach($mandatory as $field)
        {
            if (!isset($this->fieldsIndexes[$field]))
                throw new \Exception('Mandatory column '.$field.' is missing');
        }

        return true;
    }

    public function getResult()
    {
        return ['success' => $this->_results, 'errors' => $this->_errors];
    }

    public function getWarehouseId()
    {
        return $this->_warehouseId;
    }

}
