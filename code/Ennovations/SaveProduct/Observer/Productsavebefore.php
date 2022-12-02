<?php

namespace Ennovations\SaveProduct\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productsavebefore implements ObserverInterface
{    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product = $observer->getProduct();  // you will get product object
        //echo $_sku = $_product->getSku(); // for sku
        //die('Amit');
        $length = $_product->getLengthInCms();
        $width = $_product->getWidthInCms();
        $height = $_product->getHeightInCms();
        //echo $length.'=>'.$width.'=>'.$height.'</br>';

        $plength = $_product->getPackedLengthCms();
        $pwidth = $_product->getPackedWidthCms();
        $pheight = $_product->getPackedHeightCms();
        //echo $plength.'=>'.$pwidth.'=>'.$pheight.'</br>';

        //$productCBM = str_replace('.',',', number_format(($length * $width * $height ) / 1000000));
        //$packerdCBM = str_replace('.',',',number_format(($plength * $pwidth * $pheight ) / 1000000));
        $productCBM = str_replace('.',',', number_format((($length * $width * $height ) / 1000000), 2));
        $packedCBM = str_replace('.',',', number_format((($plength * $pwidth * $pheight ) / 1000000), 2));

//echo '$productCBM=>'.$productCBM.'=>'.$packerdCBM.'</br>';
//die('Amit');

        $_product->setProductCbm($productCBM);
        $_product->setPackedCbm($packedCBM);
    }   
}