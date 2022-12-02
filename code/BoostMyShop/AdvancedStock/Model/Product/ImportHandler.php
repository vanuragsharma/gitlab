<?php

namespace BoostMyShop\AdvancedStock\Model\Product;


class ImportHandler
{
    protected $csvProcessor;
    protected $fieldsIndexes = [];
    protected $_barcodesFactory;
    private $productRepository;
    protected $_advancedStockConfig;
    protected $action;

    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \BoostMyShop\AdvancedStock\Model\BarcodesFactory $barcodesFactory,
        \BoostMyShop\AdvancedStock\Model\Config $advancedStockConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->_advancedStockConfig = $advancedStockConfig;
        $this->_barcodesFactory = $barcodesFactory;
        $this->productRepository = $productRepository;
        $this->action=$action;
    }

    public function importFromCsvFile($path, $delimiter = ';')
    {

        //perform checks
        $this->csvProcessor->setDelimiter($delimiter);
        $rows = $this->csvProcessor->getData($path);
        if (!isset($rows[0]))
            throw new \Exception('The file is empty');
        $columns = $rows[0];
        $this->checkColumns($columns);

        //import rows
        $count = ['success' => 0, 'error' => 0, 'unknown' => []];
        foreach ($rows as $rowIndex => $rowData) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }
            try {
                $count = $this->_importRow($rowData, $count);
            } catch (\Exception $e) {
                throw new \Exception(__('Error: '.$e->getMessage()));
            }
        }
        return $count;
    }


    protected function _importRow($rowData,$totals)
    {
        $sku = '';
        $barcode = '';
        if (isset($this->fieldsIndexes['sku']))
            $sku = $rowData[$this->fieldsIndexes['sku']];

        if (!$sku)
            throw new \Exception(__('sku is mandatory!'));

        if (isset($this->fieldsIndexes['barcode']))
            $barcode = $rowData[$this->fieldsIndexes['barcode']];

        //transform sku to add the integration prefix
        try {
            $product = $this->getProductIdBySku($sku);
            if ($product->getId() && !empty($barcode)){
                $productBarcode = $product->getData($this->getBarcodeAttribute());
                if(!$productBarcode){
                    $this->updateAttribute($product->getId(),$this->getBarcodeAttribute(),$barcode);
                    $totals['success']++;
                }else{
                    $existBarcode = array();
                    $existBarcode[] = $productBarcode;
                    $result = $this->_barcodesFactory->create()->getProductFilter($product->getId());
                    if(count($result)>0){
                        foreach ($result as $value){
                            $existBarcode[] = $value['bac_code'];
                        }
                    }
                    if(!in_array($barcode,$existBarcode)){
                        $barcodes = $this->_barcodesFactory->create();
                        $barcodes->setData('bac_product_id',$product->getId());
                        $barcodes->setData('bac_code',$barcode);
                        $barcodes->save();
                        $totals['success']++;
                    }
                }
            }
        }catch (\Exception $e) {
            $totals['error']++;
            array_push($totals['unknown'],$sku);
        }
        return $totals;
    }

    public function checkColumns($columns)
    {
        $mandatory = [
            0 => 'sku'
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

    public function getProductIdBySku($sku)
    {
        return $this->productRepository->get($sku);
    }

    public function getBarcodeAttribute()
    {
        return $this->_advancedStockConfig->getBarcodeAttribute();
    }

    public function updateAttribute($productId, $attribute,$value,$storeId = 0)
    {
        $this->action->updateAttributes([$productId], [$attribute => $value],$storeId);
    }

}
