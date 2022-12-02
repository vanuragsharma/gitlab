<?php namespace BoostMyShop\OrderPreparation\Model\Pdf;

class PickingListA5 extends \BoostMyShop\OrderPreparation\Model\Pdf\PickingList
{
    protected $_pageSize = null;

    /**
     * Draw header for item table
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        $this->y -= 20;
        if ($this->_config->getPdfPickingLayout() != 'small') {
            $this->y -= 20;
            return;
        }

        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawLine(25, $this->y-15, 400, $this->y - 15);
        $this->y -= 10;

        //columns headers
        $lines[0][] = ['text' => __('QTY'), 'feed' => 30, 'align' => 'center'];
        $lines[0][] = ['text' => __('LOCATION'), 'feed' => 65, 'align' => 'center'];
        $lines[0][] = ['text' => __('SKU'), 'feed' => 145, 'align' => 'center'];
        $lines[0][] = ['text' => __('NAME'), 'feed' => 230, 'align' => 'center'];
        $lines[0][] = ['text' => __('EAN'), 'feed' => 355, 'align' => 'center'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document
     *
     * @param array|Collection $invoices
     * @return \Zend_Pdf
     */
    public function getPdf($orders = [])
    {
        $this->_beforeGetPdf();

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 8);
        $this->_pageSize = $this->getPdfFormat();
        if (!$this->_displaySummary && !$this->_config->pickingListOnePagePerOrder()) {
            throw new \Exception(
                'The PDF is empty because both "Include single order picklist" and "Include global picklist" options are set to "No" in the configuration.'
            );
        }
        $this->addSummaryPage($orders);

        if ($this->_config->pickingListOnePagePerOrder()) {
            foreach ($orders as $orderInProgress) {
                $tItems = [];
                foreach ($orderInProgress->getAllItems() as $item) {

                    //Exclude downloadable products
                    if ($item->getproduct_type() == "downloadable") {
                        continue;
                    }

                    $item->setproductType($item->getproduct_type());
                    $item->setLocation($this->_product->create()->getLocation($item->getproduct_id(), $this->_preparationRegistry->getCurrentWarehouseId()));
                    $item->setBarcode($this->_product->create()->getBarcode($item->getproduct_id()));
                    $item->setOptions($this->getOptionsAsText($item));
                    $item->setConfigurableOptions($this->getConfigurableOptionsAsText($item));
                    $item->setParentName($this->getParentName($item));
                    $tItems[] = $item;
                }

                //If no products to draw for current order, skip order page
                if (count($tItems) <= 0) {
                    continue;
                }

                $tItems = $this->sortPickingList($tItems);

                $page = $this->newPage();
                $this->insertBarcode($page, $orderInProgress);
                $this->drawAddresses($page, $orderInProgress->getOrder());

                $this->y -= 20;
                /* Add body */
                $this->_drawHeader($page);

                foreach ($tItems as $item) {
                    $this->_drawProduct($item, $page,false);
                    if ($this->y < 30) {
                        $page = $this->newPage();
                        $this->y -= 30;
                    }
                    $page = end($pdf->pages);
                }
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * @param $orders
     */
    protected function addSummaryPage($orders)
    {
        //get items summary
        $storeId = false;
        $items = [];
        foreach ($orders as $orderInProgress) {
            //initialize store id with first order
            if (!$storeId) {
                $storeId = $orderInProgress->getOrder()->getStoreId();
            }

            foreach ($orderInProgress->getAllItems() as $item) {

                //Exclude downloadable products from items to draw
                if ($item->getproduct_type() == "downloadable") {
                    continue;
                }

                $key = $item->getproduct_id();
                if ($this->_config->displayCustomOptionsOnPicking()) {
                    $key .= '_'.$this->getOptionsKey($item);
                }
                if (!isset($items[$key])) {
                    $obj = new \Magento\Framework\DataObject();
                    $obj->setproduct_id($item->getproduct_id());
                    $obj->setsku($item->getsku());
                    $obj->setproductType($item->getproduct_type());
                    $obj->setBarcode($this->_product->create()->getBarcode($item->getproduct_id()));
                    $obj->setMpn($this->_product->create()->getMpn($item->getproduct_id()));
                    $obj->setname($item->getname());
                    $obj->setLocation($this->_product->create()->getLocation($item->getproduct_id(), $this->_preparationRegistry->getCurrentWarehouseId()));
                    $obj->setorders([]);
                    $obj->setBinIds([]);
                    $obj->setOptions($this->getOptionsAsText($item));
                    $obj->setConfigurableOptions($this->getConfigurableOptionsAsText($item));
                    $items[$key] = $obj;
                }

                $items[$key]->setipi_qty($items[$key]->getipi_qty() + $item->getipi_qty());

                $binIds = $items[$key]->getBinIds();
                $binIds[$orderInProgress->getBinId()] = $item->getipi_qty();
                $items[$key]->setBinIds($binIds);
            }
        }

        //If no products to draw in summary page (and so in all the picking list PDF), throw error
        if (count($items) <= 0) {
            throw new \Exception(
                'The PDF is empty because there are no products to display (downloadable products are not displayed).'
            );
        }

        $items = $this->sortPickingList($items);

        $this->_eventManager->dispatch('bms_orderpreparation_picking_list_after_sort_products', ['items' => $items]);
        $page = $this->newPage();

        $this->_setFontBold($page, 18);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 5;
        $page->drawText(__('Global Picking Sheet'), 30, $this->y, 'UTF-8');
        $this->y -= 20;

        $this->_setFontRegular($page, 16);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 15;
        $page->drawText(__('Number of orders : '.count($orders)), 30, $this->y, 'UTF-8');
        $this->y -= 10;

        $this->_setFontRegular($page, 14);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_drawHeader($page);
        foreach ($items as $item) {
            $this->_drawProduct($item, $page, false);
            if ($this->y < 50) {
                $page = $this->newPage();
            }
        }
    }

    /**
     * @param $page
     * @param $barcodeNumber
     */
    protected function insertBarcode($page, $orderInProgress)
    {
        //add barcode
        $barcodeImage = $this->_barcode->getZendPdfBarcodeImage($orderInProgress->getOrder()->getIncrementId());
        $x = 82;
        $y = 560;
        $width = 253;
        $height = 80;
        $page->drawImage($barcodeImage, $x, $y - $height, $x + $width, $y);

        return $this;
    }

    /**
     * @param $item
     * @param $page
     * @param $order
     */
    protected function _drawProduct($item, $page, $drawParent = false)
    {
        switch ($this->_config->getPdfPickingLayout()) {
            case 'small':
                $row = $this->y;
                //columns headers
                $square = false;
                if($item->getipi_qty() > 1)
                    $square = true;
                $fLines[0][] = ['text' => $item->getipi_qty(), 'feed' => 30, 'align' => 'center', "square" => $square];
                $lineBlock = ['lines' => $fLines, 'height' => 2];

                $this->_setFontRegular($page,10);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $i =0;
                $this->y -=3;
                $locationLines = $this->splitTextToSize($item->getLocation(), $page->getFont(), 10, 8);
                foreach ($locationLines as $location) {
                    if(!ctype_space($location) && $location !== ''){
                        $page->drawText(substr(trim($location),0,8), 60, $this->y+5, 'UTF-8');
                        $this->y -=15;
                        $i++;
                    }
                }
                $this->y += (15*$i);
                $this->y +=5;
                //sku
                $skuLines = str_split($item->getSku(),6);;
                foreach ($skuLines as $skuLine) {
                    $page->drawText(trim($skuLine), 140, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
                $this->y += (15 * count($skuLines));
                $this->y -=5;

                $this->_setFontRegular($page,10);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                //product name
                $nameLines = $this->splitTextToSize($item->getName(), $page->getFont(), 10, 130);
                if ($this->_config->displayCustomOptionsOnPicking() && $item->getOptions()) {
                    foreach ($item->getOptions() as $option) {
                        $nameLines[] = $option;
                    }
                }
                if ($item->getConfigurableOptions()) {
                    foreach ($item->getConfigurableOptions() as $option) {
                        $nameLines[] = $option;
                    }
                }
                foreach ($nameLines as $nameLine) {
                    $page->drawText($nameLine, 195, $this->y+5, 'UTF-8');
                    $this->y -= 15;
                }
                $this->y += (15 * count($nameLines));
                $this->y +=5;
                $lines[0][] = ['text' => $item->getBarcode(), 'feed' => 330, 'align' => 'center'];
                $lineBlock = ['lines' => $lines, 'height' => 0];

                $this->_setFontRegular($page,10);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $this->y +=5;

                //add separation line
                $this->y = $row-(15 * max(count($skuLines),count(array_filter($locationLines, function($x) { return (!ctype_space($x) && $x !== ''); })),count($nameLines)));

                $page->drawLine(25, $this->y+12, 400, $this->y+12);
                $this->y -=5;
                break;
            case 'large':
                $this->_setFontRegular($page, 24);
                $page->drawText($item->getipi_qty().'x', 30, $this->y-15, 'UTF-8');

                $locationLines = $this->splitTextToSize($item->getLocation(), $page->getFont(), 12, 8);
                $i = 0;
                foreach ($locationLines as $location) {
                    $location = trim($location);
                    if(!ctype_space($location) && $location !== ''){
                        $page->drawText(substr(strtolower($location),0,6), 60, $this->y-15, 'UTF-8');
                        $this->y -= 20;
                        $i -= 5;
                    }
                }
                $this->y -= $i;

                $this->_setFontRegular($page, 14);
                $page->drawText($item->getSku(), 130, $this->y, 'UTF-8');

                $finalOffset = 50;

                $nameLines = $this->splitTextToSize($item->getName(), $page->getFont(), 14, 200);
                foreach ($nameLines as $nameLine) {
                    $page->drawText($nameLine, 200, $this->y, 'UTF-8');
                    $this->y -= 15;
                    $finalOffset -= 10;
                }

                if ($this->_config->displayCustomOptionsOnPicking() && $item->getOptions()) {
                    foreach ($item->getOptions() as $option) {
                        $page->drawText($option, 150, $this->y - 5, 'UTF-8');
                        $this->y -= 20;
                        $finalOffset -= 5;
                    }
                }

                if ($item->getConfigurableOptions()) {
                    foreach ($item->getConfigurableOptions() as $option) {
                        $page->drawText($option, 150, $this->y - 5, 'UTF-8');
                        $this->y -= 20;
                        $finalOffset -= 5;
                    }
                }

                $this->y -= $finalOffset;
                $this->y -= 12;
                $page->drawLine(25, $this->y + 26, 380, $this->y + 26);
                $this->y -= 5;

                break;
        }
    }

    /**
     * Insert shipto blocks
     *
     * @param $page
     * @param $order
     */
    protected function drawAddresses($page, $order)
    {
        $this->y -=80;
        /* Add table head */
        $this->_setFontRegular($page, 14);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        $this->y -= 20;
        $page->drawText(__('Order ID #'.$order->getincrement_id()), 30, $this->y, 'UTF-8');
        $page->drawText(__('Date : '.$order->getcreated_at()), 30, $this->y-15, 'UTF-8');
        $page->drawText(__('Carrier : '.$order->getshipping_description()), 30, $this->y-30, 'UTF-8');
        $page->drawText(__('Number of products : '.count($order->getAllItems())), 30, $this->y-45, 'UTF-8');


        $page->drawText(__('Ship to :'), 30, $this->y-80, 'UTF-8');

        if ($order->getShippingAddress()) {
            $shippingAddress = $this->addressRenderer->format($order->getShippingAddress(), 'html');
            $shippingAddress = str_replace("\n", "", $shippingAddress);
            $shippingAddress = str_replace("<br />", "<br/>", $shippingAddress);
            $shippingAddress = str_replace("&#039;", " ", $shippingAddress);
            $i = 0;
            $shippingAddress = wordwrap($shippingAddress, 50, "<br/>");
            foreach (explode("<br/>", $shippingAddress) as $line) {
                $line = str_replace(chr(13), "", $line);
                $line = strip_tags($line);
                if ($line) {
                    $page->drawText($line, 30, $this->y - 100 - ($i * 13), 'UTF-8');
                    $i++;
                }
            }
        }
        $this->y -= 90+(10*$i);
    }

    public function displaySummary($value)
    {
        $this->_displaySummary = $value;
        return $this;
    }

    public function getCurrentUser()
    {
        return $this->_userFactory->create()->load($this->_preparationRegistry->getCurrentOperatorId());
    }

    public function getCurrentWebsiteId()
    {
        return  $this->getCurrentUser()->geterpcloud_website_id();
    }

    public function getPdfFormat()
    {
        $pdfFormat = null;
        if($this->getCurrentWebsiteId()){
            $pdfFormat = $this->_scopeConfig->getValue('orderpreparation/picking/print_format', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $this->getCurrentWebsiteId());
        }
        return $pdfFormat;

    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return \Zend_Pdf_Page
     */
    public function newPage(array $settings = [])
    {
        /* Add new table head */
        $pageSize = $this->_pageSize ? $this->_pageSize : \Zend_Pdf_Page::SIZE_A4;
        $page = $this->_getPdf()->newPage($pageSize);
        $this->_getPdf()->pages[] = $page;
        $this->y = 520;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }

        return $page;
    }
    protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA);
        $object->setFont($font, $size);
        return $font;
    }
    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
        $object->setFont($font, $size);
        return $font;
    }


}
