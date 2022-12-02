<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Model;

class Product
{
    protected $_warehouseItemFactory;
    protected $_barcodesFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \BoostMyShop\AdvancedStock\Model\BarcodesFactory $barcodesFactory
    )
    {
        $this->_warehouseItemFactory= $warehouseItemFactory;
        $this->_barcodesFactory= $barcodesFactory;

    }

    public function aroundGetLocation(\BoostMyShop\OrderPreparation\Model\Product $subject, $proceed, $productId, $warehouseId)
    {
        $item = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $warehouseId);
        if ($item)
            return $item->getwi_shelf_location();
    }
    public function aroundGetAdditionalBarcodes(\BoostMyShop\OrderPreparation\Model\Product $subject, $proceed, $productId)
    {
        $barcodeArray = [];
        $barcodes = $this->_barcodesFactory->create()->getProductFilter($productId);
        foreach ($barcodes as $barcode){
            $barcodeArray[] = $barcode['bac_code'];
        }
        if ($barcodeArray)
            return json_encode($barcodeArray);
    }

}