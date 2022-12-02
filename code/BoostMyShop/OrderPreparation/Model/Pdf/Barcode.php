<?php

namespace BoostMyShop\OrderPreparation\Model\Pdf;

use Magento\Framework\App\Filesystem\DirectoryList;

class Barcode
{
    protected $_filesystem;
    protected $_barcodeGenerator;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \BoostMyShop\OrderPreparation\Model\Pdf\BarcodeGenerator $barcodeGenerator
    ) {
        $this->_filesystem = $filesystem;
        $this->_barcodeGenerator = $barcodeGenerator;
    }

    /**
     * Return a GDI barcode image
     * @param $barcode
     * @return mixed
     */
    public function createBarcodeImage($barcode)
    {
        if (class_exists("Zend_Barcode"))
        {
            $barcodeStandard = 'Code128';
            $barcodeOptions = array('text' => $barcode);
            $rendererOptions = array();
            $factory = \Zend_Barcode::factory($barcodeStandard, 'image', $barcodeOptions, $rendererOptions);
            $image = $factory->draw();
        }
        else
        {
            //Zend_Barcode class removed from magento 2.3 :(
            $barcodeStandard = 'Code128';
            $image = $this->_barcodeGenerator->generateBarcodeImage($barcode, $barcodeStandard);
        }
        return $image;
    }

    /**
     * Return a zend pdf image
     *
     * @param $barcode
     */
    public function getZendPdfBarcodeImage($barcode)
    {
        $tempImage = $this->createBarcodeImage($barcode);

        if (!is_dir($this->_filesystem->getDirectoryWrite(DirectoryList::TMP)->getAbsolutePath()))
            mkdir($this->_filesystem->getDirectoryWrite(DirectoryList::TMP)->getAbsolutePath());
        $tempPath = $this->_filesystem->getDirectoryWrite(DirectoryList::TMP)->getAbsolutePath('bms_orderpreparation_barcodelabel.png');
        imagepng($tempImage, $tempPath);
        $zendPicture = \Zend_Pdf_Image::imageWithPath($tempPath);
        unlink($tempPath);
        return $zendPicture;
    }
}