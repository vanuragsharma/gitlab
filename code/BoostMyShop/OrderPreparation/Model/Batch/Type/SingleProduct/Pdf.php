<?php
namespace BoostMyShop\OrderPreparation\Model\Batch\Type\SingleProduct;

class Pdf extends \BoostMyShop\OrderPreparation\Model\Batch\Type\AbstactPdf
{
    protected function insertTypeData(&$page, $batch)
    {
        $itemData = [];
        foreach ($batch->getBatchOrderItems() as $item)
        {
            $product = $item->getOrderItem()->getProduct();
            if(!$product)
                continue;

            $itemData[$product->getId()]['sku'] = $this->cleanReference($product->getSku());
            $itemData[$product->getId()]['name'] = $product->getName();


            if(!isset($itemData[$product->getId()]['qty'])) {
                $itemData[$product->getId()]['qty'] = $item->getipi_qty();
            }
            else{
                $itemData[$product->getId()]['qty'] = $itemData[$product->getId()]['qty'] + $item->getipi_qty();
            }

            $location = $this->_opProduct->getLocation($item->getOrderItem()->getProduct()->getId(), $batch->getbob_warehouse_id());
            $itemData[$product->getId()]['location'] = $location;

            $imagePath = $this->_opProduct->getImagePath($item->getOrderItem()->getProduct()->getId());
            $image = $this->_mediaDirectory->isFile($imagePath)?$this->_mediaDirectory->getAbsolutePath($imagePath):null;
            $itemData[$product->getId()]['image_path'] = $image;

            if($this->_config->isAdvancedstockModuleInstall())
            {
                $warehouseItemFactory = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory');
                $stock = $warehouseItemFactory->create()->loadByProductWarehouse($item->getOrderItem()->getProduct()->getId(), $batch->getbob_warehouse_id());
                $itemData[$product->getId()]['stock'] = $stock->getwi_physical_quantity();
            } else {
                $itemData[$product->getId()]['stock'] = $this->_opProduct->getMagentoQty($product->getId(), $product->getStore()->getWebsiteId());
            }
        }

        $page = $this->drawItems($page, $itemData);
    }

    protected function drawItems(\Zend_Pdf_Page $page, $itemData)
    {
        if($this->y < 25)
            $page = $this->newPage();

        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);

        //columns headers
        $w = $this->_width-40;
        $x1 = 20;
        $x2 = $x1+($w*13/100);
        $x3 = $x2+($w*10/100);
        $x4 = $x3+($w*20/100);
        $x5 = $x4+($w*30/100);
        $x6 = $x5+($w*15/100);
        $x7 = $x6+($w*12/100);

        $page->drawRectangle($x1, $this->y, $x2, $this->y - 15);
        $page->drawRectangle($x2, $this->y, $x3, $this->y - 15);
        $page->drawRectangle($x3, $this->y, $x4, $this->y - 15);
        $page->drawRectangle($x4, $this->y, $x5, $this->y - 15);
        $page->drawRectangle($x5, $this->y, $x6, $this->y - 15);
        $page->drawRectangle($x6, $this->y, $x7, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $lines = [];
        $lines[0][] = ['text' => __('Image'), 'feed' => $x1+5 , 'align' => "center", 'width' => $x2-$x1-5];
        $lines[0][] = ['text' => __('Qty'), 'feed' => $x2+5 , 'align' => "center", 'width' => $x3-$x2-5];
        $lines[0][] = ['text' => __('Sku'), 'feed' => $x3+5, 'align' => 'center', 'width' => $x4-$x3-5];
        $lines[0][] = ['text' => __('Name'), 'feed' => $x4+5, 'align' => 'center', 'width' => $x5-$x4-5];
        $lines[0][] = ['text' => __('Location'), 'feed' => $x5+5, 'align' => 'center', 'width' => $x6-$x5-5];
        $lines[0][] = ['text' => __('Stock'), 'feed' => $x6+5, 'align' => 'center', 'width' => $x7-$x6-5];
        $lineBlock = ['lines' => $lines, 'height' => 5];
        unset($lines);

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);

        usort($itemData, function($element1, $element2) {
            $data1 = $element1['location'];
            $data2 = $element2['location'];
            if($data1 && !$data2)
                return false;
            if(!$data1 && $data2)
                return true;
            else
                return $data1 > $data2;
        });

        foreach ($itemData as $item) {
            $sku = $this->string->split($item['sku'], 12);
            $name = $this->string->split($item['name'], 17);
            $location = $this->string->split($item['location'], 10);
                $count = count($name) > count($sku)?(count($name) > count($location)?count($name):count($location)):(count($sku) > count($location)?count($sku):count($location));

            if($this->y - (10*$count)-10 < 25)
                $page = $this->newPage();

            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
            $page->drawRectangle($x1, $this->y, $x2, $this->y - (10*$count)-10);
            $page->drawRectangle($x2, $this->y, $x3, $this->y - (10*$count)-10);
            $page->drawRectangle($x3, $this->y, $x4, $this->y - (10*$count)-10);
            $page->drawRectangle($x4, $this->y, $x5, $this->y - (10*$count)-10);
            $page->drawRectangle($x5, $this->y, $x6, $this->y - (10*$count)-10);
            $page->drawRectangle($x6, $this->y, $x7, $this->y - (10*$count)-10);
            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
            if($item['image_path'])
            {
                $image = null;
                try{
                    $image = \Zend_Pdf_Image::imageWithPath($item['image_path']);
                } catch(\Exception $ex)
                {
                    //nothing
                }
                if ($image)
                    $page->drawImage($image, $x1+5, $this->y - (8*$count), $x2-5, $this->y+5);
            }

            $lines = [];
            $lines[0][] = ['text' => (string)$item['qty'], 'feed' => $x2+5, 'align' => "center", 'width' => $x3-$x2-5];
            $lines[0][] = ['text' => $sku, 'feed' => $x3+5, 'height'=> 10, 'align' => 'center', 'width' => $x4-$x3-5];
            $lines[0][] = ['text' => $name, 'feed' => $x4+5, 'height'=> 10, 'align' => 'center', 'width' => $x5-$x4-5];
            $lines[0][] = ['text' => $location, 'feed' => $x5+5, 'height'=> 10, 'align' => 'center', 'width' => $x6-$x5-5];
            $lines[0][] = ['text' => (string)$item['stock'], 'feed' => $x6+5, 'height'=> 10, 'align' => 'center', 'width' => $x7-$x6-5];
            $lineBlock = ['lines' => $lines, 'height' => 5];
            unset($lines);
            $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        }

        $this->y -= 20;
        return $page;
    }
}