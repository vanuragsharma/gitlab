<?php

namespace BoostMyShop\Supplier\Model\Supplier\Product;


class ImportHandler
{

    protected $csvProcessor;

    protected $fieldsIndexes = [];

    protected $_results = [];

    protected $_productFactory;
    protected $_supplierFactory;
    protected $_supplierProductFactory;

    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory,
        \BoostMyShop\Supplier\Model\Supplier\ProductFactory $supplierProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->_supplierFactory = $supplierFactory;
        $this->_productFactory = $productFactory;
        $this->_supplierProductFactory = $supplierProductFactory;
    }

    public function importFromCsvFile($path, $delimiter = ";")
    {

        //perform checks
        $this->csvProcessor->setDelimiter($delimiter);
        $rows = $this->csvProcessor->getData($path);
        if (!isset($rows[0]))
            throw new \Exception('The file is empty');
        $columns = $rows[0];
        $this->checkColumns($columns);

        //import rows
        $count = 0;
        foreach ($rows as $rowIndex => $rowData) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }

            try
            {
                $this->importRow($rowData, $rowIndex);
                $count++;
            }
            catch(\Exception $ex)
            {
                $this->_results[] = 'Row #'.$rowIndex.' : '.$ex->getMessage();
            }

        }

        return $count;
    }



    public function importRow($rowData, $rowIndex)
    {
        //load supplier
        $code = '';
        if (isset($this->fieldsIndexes['supplier']))
            $code = $rowData[$this->fieldsIndexes['supplier']];
        if (!$code)
            throw new \Exception('supplier is missing');
        $supplier = $this->loadSupplierByCode($code);

        //load product
        $sku = '';
        if (isset($this->fieldsIndexes['sku']))
            $sku = $rowData[$this->fieldsIndexes['sku']];
        if (!$sku)
            throw new \Exception('sku is missing');
        $productId = $this->getProductId($sku);
        if (!$productId)
            throw new \Exception('product with "'.$sku.'" not found');

        //process association
        if (!$supplier->isAssociatedToProduct($productId))
            $supplier->associateProduct($productId);
        $productSupplier = $this->_supplierProductFactory->create()->loadByProductSupplier($productId, $supplier->getId());
        foreach($this->fieldsIndexes as $k => $index)
        {
            $productSupplier->setData($k, $rowData[$this->fieldsIndexes[$k]]);
        }
        $productSupplier->save();


        return true;
    }

    public function loadSupplierByCode($code)
    {
        $supplier = $this->_supplierFactory->create()->load($code, 'sup_code');
        if (!$supplier->getId())
            throw new \Exception('supplier with code "'.$code.'" not found');
        return $supplier;
    }

    public function getProductId($sku)
    {
        $productId = $this->_productFactory->create()->getIdBySku($sku);
        return $productId;
    }

    public function checkColumns($columns)
    {
        $columns = $this->translateColumns($columns);

        $mandatory = [
            0 => 'sku',
            1 => 'supplier',
        ];
        for($i=0;$i<count($columns);$i++)
        {
            $this->fieldsIndexes[$columns[$i]] = $i;
        }

        foreach($mandatory as $field)
        {
            if (!isset($this->fieldsIndexes[$field]))
                throw new \Exception('Mandatory column '.$field.' is missing');
        }

        return true;
    }

    public function getResults()
    {
        return $this->_results;
    }

    public function translateColumns($columns)
    {

        for($i=0;$i<count($columns);$i++)
        {
            $columns[$i] = strtolower($columns[$i]);
            switch($columns[$i])
            {
                case 'sku fournisseur':
                case 'supplier sku':
                    $columns[$i] = 'sp_sku';
                    break;
                case 'code four.':
                case 'supplier code':
                    $columns[$i] = 'supplier';
                    break;
                case 'prix d\'achat':
                case 'buying price':
                    $columns[$i] = 'sp_price';
                    break;
                case 'qté min':
                case 'moq':
                    $columns[$i] = 'sp_moq';
                    break;
                case 'pack':
                case 'pack qty':
                    $columns[$i] = 'sp_pack_qty';
                    break;
                case 'principal?':
                case 'is primary':
                    $columns[$i] = 'sp_primary';
                    break;
                case 'stock fournisseur':
                case 'supplier stock':
                    $columns[$i] = 'sp_stock';
                    break;
            }
        }

        return $columns;
    }

}
