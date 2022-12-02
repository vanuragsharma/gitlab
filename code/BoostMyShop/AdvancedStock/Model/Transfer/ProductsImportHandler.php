<?php

namespace BoostMyShop\AdvancedStock\Model\Transfer;


class ProductsImportHandler
{

    protected $csvProcessor;
    protected $_product;
    protected $fieldsIndexes = [];
    protected $_transferFactory;
    protected $_productFactory;

    protected $_results = [];

    protected $_barcodeAttribute;

    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \BoostMyShop\AdvancedStock\Model\TransferFactory $transferFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->_product = $product;
        $this->_productFactory = $productFactory;
        $this->_transferFactory = $transferFactory;
        $this->_config = $config;
    }

    public function importFromCsvFile($transferId, $filePath, $separator = ";")
    {
        $transfer = $this->_transferFactory->create()->load($transferId);
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

            try
            {
                if ($this->_importRow($transfer, $rowData))
                    $count++;
            }
            catch(\Exception $ex)
            {
                $this->_results[] = $ex->getMessage();
            }
        }

        return $count;
    }



    protected function _importRow($transfer, $rowData)
    {
        $sku = (isset($this->fieldsIndexes['sku']) ? $rowData[$this->fieldsIndexes['sku']] : '');
        $qty = (isset($this->fieldsIndexes['qty']) ? $rowData[$this->fieldsIndexes['qty']] : '');
        $barcode = (isset($this->fieldsIndexes['barcode']) ? $rowData[$this->fieldsIndexes['barcode']] : '');

        //find product (by sku OR barcode)
        $productId = false;
        if ($sku)
            $productId = $this->getProductIdBySku($sku);
        if (!$productId) {
            if ($barcode && $this->_barcodeAttribute)
            {
                $product = $this->_productFactory->create()->loadByAttribute($this->_barcodeAttribute, $barcode);
                if (!$product)
                {
                    throw new \Exception('Unknown sku : '.$sku.' / barcode '.$barcode);
                }
                else
                    $productId = $product->getId();
            }
            else
            {
                throw new \Exception('Unknown sku : '.$sku);
            }
        }

        $transfer->addOrUpdateQty($productId, $qty);

        return true;
    }

    public function getProductIdBySku($sku)
    {
        return $this->_product->getIdBySku($sku);
    }


    public function checkColumns($columns)
    {
        $mandatory = [
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

    public function getResult()
    {
        return $this->_results;
    }

}
