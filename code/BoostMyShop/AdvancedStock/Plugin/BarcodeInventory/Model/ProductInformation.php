<?php

namespace BoostMyShop\AdvancedStock\Plugin\BarcodeInventory\Model;

class ProductInformation
{
    protected $_barcodeCollectionFactory;
    protected $_collectionFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Barcodes\CollectionFactory $barcodeCollectionFactory
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_barcodeCollectionFactory= $barcodeCollectionFactory;
    }

    public function afterGetIdFromBarcode(\BoostMyShop\BarcodeInventory\Model\ProductInformation $subject, $result, $barcode)
    {
        if($result)
            return $result;

        //barcode attribute search
        $item = $this->_collectionFactory->create()->addAttributeToFilter('barcode', $barcode)->getFirstItem();
        if ($item->getId())
            return $item->getId();


        // check for additional barcodes
        $barcodeCollection = $this->_barcodeCollectionFactory->create()->addFieldToSelect('bac_product_id')->addFieldToFilter('bac_code', array('eq' => $barcode));
        if($barcodeCollection->getSize()){
            $advancedBarcode = $barcodeCollection->getFirstItem();
            return $advancedBarcode->getbac_product_id();
        }

        return false;

    }

}
