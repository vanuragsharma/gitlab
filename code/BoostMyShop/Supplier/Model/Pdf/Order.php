<?php

namespace BoostMyShop\Supplier\Model\Pdf;
use Magento\Framework\App\Filesystem\DirectoryList;


class Order extends AbstractPdf
{

    protected $_storeManager;
    protected $_localeResolver;
    protected $_emulation;

    protected $_config;
    protected $_product;

    protected $_skuFeed = 70;
    protected $_nameFeed = 230;
    protected $_eventManager;

    protected $_priceFeed;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \BoostMyShop\Supplier\Model\ConfigFactory $config,
        \Magento\Framework\Event\Manager $eventManager,
        \BoostMyShop\Supplier\Model\Product $product,
        \Magento\Store\Model\App\Emulation $emulation,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_localeResolver = $localeResolver;
        $this->_config = $config;
        $this->_product = $product;
        $this->_eventManager = $eventManager;
        $this->_emulation = $emulation;

        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $data
        );
    }

    /**
     * Draw header for item table
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;

        if ($this->_config->create()->getSetting('order_product/enable_discount'))
            $this->_priceFeed = 530;
        else
            $this->_priceFeed = 580;

        //columns headers
        $lines[0][] = ['text' => __('Qty'), 'feed' => 45, 'align' => 'right'];
        if($this->_config->create()->getSetting('general/pack_quantity')){
            $this->_skuFeed = 90;
            $lines[0][] = ['text' => __('Pack qty'), 'feed' => 50, 'align' => 'left'];
        }
        $lines[0][] = ['text' => __('SKU'), 'feed' => $this->_skuFeed, 'align' => 'left'];
        $lines[0][] = ['text' => __(' '), 'feed' => 155, 'align' => 'left'];
        $lines[0][] = ['text' => __('Product'), 'feed' => 230, 'align' => 'left'];
        $lines[0][] = ['text' => __('CBM'), 'feed' => 405, 'align' => 'left'];
        $lines[0][] = ['text' => __('Total CBM'), 'feed' => 435, 'align' => 'left'];
        $lines[0][] = ['text' => __('Price'), 'feed' => 505, 'align' => 'right'];
        //if ($this->_config->create()->getSetting('order_product/enable_discount'))
            //$lines[0][] = ['text' => __('Discount'), 'feed' => 490, 'align' => 'right'];

        $lines[0][] = ['text' => __('Total'), 'feed' => 555, 'align' => 'right'];

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

        foreach ($orders as $order) {

            if ($order->getPoStoreId()) {
                //$this->_localeResolver->emulate($order->getPoStoreId());
                //$this->_storeManager->setCurrentStore($order->getPoStoreId());
                $this->_emulation->startEnvironmentEmulation($order->getPoStoreId(), \Magento\Framework\App\Area::AREA_FRONTEND, true);
            }
            $page = $this->newPage(['store_id' => $order->getPoStoreId()]);

            /* Add image */
            $this->insertLogo($page, $order->getStore());

            /* Add document text and number */
            $this->drawPoInformation($page, $order);

            $this->drawAddresses($page, $order);

            /* Add Warehouse instructions section*/
            $this->_eventManager->dispatch('bms_supplier_order_pdf_before_products', ['order' => $order, 'page' => $page, 'pdf' => $this]);

            $this->drawPublicComments($page, $order);

            /* Add shipping instructions section*/
            $this->drawSupplierInstruction($page, $order);

            $this->_drawHeader($page);
            $totalCBM = [];
            /* Add body */
            foreach ($order->getAllItems() as $item) {
		
                //check available space
                if ($this->y < 100)
                    $page = $this->newPage(['store_id' => $order->getPoStoreId()]);

                /* Draw item */
                $this->_drawItem($item, $page, $order);

                $page = end($pdf->pages);
            }

            $this->insertCosts($page, $order);

            $this->insertTotals($page, $order);

            $this->insertAdditionnal($page, $order);

            $this->_eventManager->dispatch('bms_supplier_order_pdf_end', ['order' => $order, 'page' => $page, 'pdf' => $this]);

            if ($order->getPoStoreId()) {
                //$this->_localeResolver->revert();
                $this->_emulation->stopEnvironmentEmulation();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
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
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }

        $storeId = (isset($settings['store_id']) ? $settings['store_id'] : 0);
        $footerTxt = $this->_config->create()->getSetting('pdf/footer', $storeId);
        if(trim(strlen($footerTxt) > 0))
            $page = $this->insertFooter($page, trim($footerTxt));

        return $page;
    }

    protected function insertFooter($page, $footerTxt)
    {
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $this->_setFontRegular($page, 10);
        $page->setLineWidth(0);
        $top = 30;
        $footerTxtArray = explode("\n", $footerTxt);
        foreach (array_reverse($footerTxtArray) as $value) {
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                $valueLines = array_reverse($this->string->split($value, 122, true, true));
                foreach ($valueLines as $_value) {
                    $page->drawText(
                        trim(strip_tags($_value)),
                        30,
                        $top,
                        'UTF-8'
                    );
                    $top += 15;
                }
            }
        }
        return $page;
    }

    /**
     * @param $item
     * @param $page
     * @param $order
     */
    protected function _drawItem($item, $page, $order)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
        $product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getPopProductId());
        $cbm = str_replace(',', '.', trim($product->getProductCbm()));
        if ($product->getProductCbm() == '') {
            $cbm = 0;
        }
        if ($item->getPopQty() == 0) {
            $PopQty = 1;
        } else {
            $PopQty = $item->getPopQty();
        }

        if(isset($cbm)) {
            $totalCbm = $PopQty * $cbm;
        } else {
            $totalCbm = $PopQty;
        }
        $totalCbm1 = 0;
        if ($product->getProductCbm()) {
            $cbm = str_replace(',', '.', trim($product->getProductCbm()));
            $totalCbm1 = $item->getPopQty()*$cbm;
        }
        $this->setCustomCBMTotal($totalCbm1);
        /* Add table head */
        $this->_setFontRegular($page, 9);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        //columns headers
        $lines[0][] = ['text' => $item->getPopQty(), 'feed' => 38, 'align' => 'right'];
        if($this->_config->create()->getSetting('general/pack_quantity'))
            $lines[0][] = ['text' => 'x'.$item->getpop_qty_pack(), 'feed' => 50, 'align' => 'left'];

        $lines[0][] = ['text' => $cbm, 'feed' => 407, 'align' => 'left'];
        $lines[0][] = ['text' => $totalCbm1, 'feed' => 437, 'align' => 'left'];

        $lines[0][] = ['text' => $item->getPopPrice(), [], false, 'feed' => 520, 'align' => 'right'];

        //if ($this->_config->create()->getSetting('order_product/enable_discount') && ($item->getPopDiscountPercent() > 0))
            //$lines[0][] = ['text' => $item->getPopDiscountPercent().'%', 'feed' => 490, 'align' => 'right'];


        $lines[0][] = ['text' => $order->getCurrency()->format($item->getPopSubtotal(), [], false), 'feed' => 565, 'align' => 'right'];
        $lineBlock = ['lines' => $lines, 'height' => 5];

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        $this->y += 5;

        //1) PREPARE DATA

        //SKU
        $maxWidth = 200 - $this->_skuFeed -7;
        $skuLines = $this->splitTextToSize($item->getPopSku(), $page->getFont(), 10, $maxWidth);

        //SUPPLIER SKU
        $supplierSkuLines = [];
        if ($item->getPopSupplierSku() && ($item->getPopSupplierSku() != $item->getPopSku()))
            $supplierSkuLines = $this->splitTextToSize($item->getPopSupplierSku(), $page->getFont(), 10, 100);

        //PRODUCT NAME
        $nameLines = $this->splitTextToSize($item->getPopName(), $page->getFont(), 10, 180);
        $barcode = $this->_product->getBarcode($item->getpop_product_id());

        if ($barcode)
            $nameLines[] = __('Barcode').': '.$barcode;
        $location = $this->_product->getLocation($item->getpop_product_id(), $order->getpo_warehouse_id());
        if ($location)
            $nameLines[] = __('Location').': '.$location;
        $mpn = $this->_product->getMpn($item->getpop_product_id());
        if ($mpn)
            $nameLines[] = __('Mpn').': '.$mpn;

        //1) DISPLAY DATA

        //top y baseline
        $baseDisplayY = $this->y;
        $interlineHeight = 25;
        $imagePosition = 70;
        $imagePositionHeigh = 1;
        //DISPLAY SKU
        foreach($skuLines as $skuLine){
            $page->drawText($skuLine, $this->_skuFeed, $this->y, 'UTF-8');
            $this->y -= $interlineHeight;
        }
        foreach($nameLines as $nameLine) {
            $imagePositionHeigh += 1;            
        }
        if($imagePositionHeigh == 2){
            $imagePosition += 15;
            $this->y -= 20;
        }elseif($imagePositionHeigh == 3){
            $imagePosition += 10;
            $this->y -= 20;
        } else{
            $imagePosition += 0;
        }

        $this->y -= $interlineHeight;

        //DISPLAY SUPPLIER SKU
        if ($item->getPopSupplierSku()) {
            foreach ($supplierSkuLines as $supplierSkuLine) {
                $page->drawText($supplierSkuLine, $this->_skuFeed, $this->y, 'UTF-8');
                $this->y -= $interlineHeight;
            }
        }
        $endDisplayYAfterSku = $this->y;

        // Display Product Image
		$image = false;
        $image = $this->getProductImage($item);
        if ($image) {
            $page->drawImage($image, 165, $this->y + ($imagePosition - 60), 215, $this->y + $imagePosition);
            // $page->drawImage($image, 160, $this->y + ($imagePosition - 50), 225, $this->y + ($imagePosition + 10));
        }
        $this->y -= $interlineHeight;

        //SET GAIN Y BASELINE TO VERTICAL ALIGN
        $this->y = $baseDisplayY;

        //DISPLAY PRODUCT NAME
        foreach($nameLines as $nameLine) {
            $page->drawText($nameLine, $this->_nameFeed, $this->y, 'UTF-8');
            $this->y -= $interlineHeight;
        }
        $endDisplayYAfterName = $this->y;

        //keep the lowest Y to avoid text override
        $this->y = ($endDisplayYAfterSku<$endDisplayYAfterName)?$endDisplayYAfterSku:$endDisplayYAfterName;

        $page->drawLine(25, $this->y + 3, 570, $this->y + 3);

        //bottom item margin
        $this->y -= $interlineHeight;

    }

    /**
     * @param $page
     * @param $order
     */
    protected function insertCosts($page, $order)
    {
        $costs = ['Shipping' => $order->getpo_shipping_cost(), 'Additionnal' => $order->getpo_additionnal_cost()];
        foreach($costs as $label => $value)
        {
            if ($value > 0)
            {
                $lines = [];
                $this->_setFontRegular($page, 10);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
                $lines[0][] = ['text' => $label, 'feed' => 200, 'align' => 'left'];
                $lines[0][] = ['text' => $order->getCurrency()->format($value, [], false), 'feed' => $this->_priceFeed, 'align' => 'right'];
                $lines[0][] = ['text' => $order->getCurrency()->format($value, [], false), 'feed' => 550, 'align' => 'right'];
                $lineBlock = ['lines' => $lines, 'height' => 5];

                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $this->y -= 20;
            }
        }

    }

    /**
     * Insert totals to pdf page
     *
     * @param  \Zend_Pdf_Page $page
     * @param  \Magento\Sales\Model\AbstractModel $source
     * @return \Zend_Pdf_Page
     */
    protected function insertTotals($page, $order)
    {

         $totalCbm = 0;
         $totalCbm1 = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        foreach ($order->getAllItems() as $item) {
            $product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getPopProductId());
            if ($product->getProductCbm()) {
                $cbm = str_replace(',', '.', trim($product->getProductCbm()));
                $totalCbm+= $item->getPopQty()*$cbm;
                $totalCbm1[$product->getSku() . '- ' . $product->getProductCbm()] = $item->getPopQty()*$cbm;
            }
        }
        //echo "<pre>"; print_r($this->$totalCbm1); exit;
        $totals = [];
        $totals[] = ['label' => __('Subtota CBM'), 'value' => array_sum($this->cbmTotal)];
        $totals[] = ['label' => __('Subtotal'), 'value' => $order->getPoSubtotal()];
        if ($order->getGlobalDiscountAmount() > 0)
            $totals[] = ['label' => __('Discount').' ('.$order->getpo_global_discount().'%)', 'value' => $order->getGlobalDiscountAmount()];
        $totals[] = ['label' => __('Shipping & additional'), 'value' => $order->getPoShippingCost() + $order->getPoAdditionnalCost()];
        $totals[] = ['label' => __('Taxes'), 'value' => $order->getPoTax()];
        $totals[] = ['label' => __('Grand total'), 'value' => $order->getPoGrandtotal()];

        //$page->drawLine(25, $this->y, 570, $this->y);
        $this->y -= 20;

        $this->_setFontBold($page, 18);

        //check available space
        if ($this->y < 100)
            $page = $this->newPage(['store_id' => $order->getPoStoreId()]);

        foreach($totals as $total)
        {
            $lines = [];
            $lines[0][] = ['text' => __($total['label']), 'font_size' => 14, 'feed' => 350, 'align' => 'left'];
            if($total['label'] == 'Subtota CBM'){
                 $lines[0][] = ['text' => $total['value'], 'font_size' => 14, 'feed' => 550, 'align' => 'right'];
            }else{
                 $lines[0][] = ['text' => $order->getCurrency()->format($total['value'], [], false), 'font_size' => 14, 'feed' => 550, 'align' => 'right'];
            }

            $lineBlock = ['lines' => $lines, 'height' => 20];
            $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);

        }

        return $page;
    }

    /**
     * Insert billto & shipto blocks
     *
     * @param $page
     * @param $order
     */
    protected function drawAddresses($page, $order)
    {
        /* Add table head */
        $this->_setFontBold($page, 14);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        $this->y -= 15;
        $page->drawText(__('Bill to :'), 30, $this->y, 'UTF-8');
        $page->drawText(__('Ship to :'), 300, $this->y, 'UTF-8');

        $billingAddress = explode("\n", $order->getBillingAddress());
        //$shippingAddress = explode("\n", $order->getShippingAddress());
        //Get the default shipping address form configuration
        $shippingAddress = explode("\n", $this->getShippingAddress());

        $this->_setFontRegular($page, 12);
        $i = 0;
        foreach($billingAddress as $line) {
            $line = str_replace("\r", "", $line);
            $lines = $this->splitTextToSize($line, $page->getFont(), 12, 250);
            foreach($lines as $line)
            {
                $page->drawText($line, 40, $this->y - 20 - ($i * 13), 'UTF-8');
                $i++;
            }
        }

        $j = 0;
        foreach($shippingAddress as $line) {
            $line = str_replace("\r", "", $line);
            $lines = $this->splitTextToSize($line, $page->getFont(), 12, 260);
            foreach($lines as $line)
            {
                $page->drawText($line, 310, $this->y - 20 - ($j * 13), 'UTF-8');
                $j++;
            }
        }

        $maxLines = max(($i), ($j));

        $this->y -= $maxLines * 20 + 20;
    }

    protected function drawPoInformation($page, $order)
    {

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 105);

        $this->_setFontBold($page, 14);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__('Purchase Order # %1', $order->getPoReference()), 30, $this->y - 20, 'UTF-8');

        $this->_setFontRegular($page, 12);
        $additionnalTxt = [];
        $additionnalTxt[] = __('Supplier : %1', $order->getSupplier()->getsup_name());
        $additionnalTxt[] = __('Manager : %1', $order->getManager()->getfirstname().' '.$order->getManager()->getlastname());
        $additionnalTxt[] = __('Order date : %1', $order->formatDate($order->getpo_created_at()));
        $additionnalTxt[] = __('Estimated delivery : %1', $order->formatDate($order->getpo_eta()));
        if ($order->getSupplier()->getsup_payment_terms())
            $additionnalTxt[] = __('Payment terms : %1', $order->getSupplier()->getsup_payment_terms());
        if ($order->getpo_supplier_reference())
            $additionnalTxt[] = __('Supplier order # : %1', $order->getpo_supplier_reference());
        $i = 0;
        foreach($additionnalTxt as $txt)
        {
            $page->drawText($txt, 60, $this->y - 40 - ($i * 13), 'UTF-8');
            $i++;
        }

        $this->y -= 115;

    }

    protected function drawPublicComments($page, $order)
    {
        $comments = $order->getpo_public_comments();

        if (!$comments)
            return $this;

        $comments = explode("\r\n", $comments);
        $lineCount = count($comments) + 1;
        foreach ($comments as $line){
            $linesSplitToSize = $this->splitTextToSize($line, $page->getFont(), 12, 485);
            foreach($linesSplitToSize as $index => $text)
            {
                $lineCount += $index > 0 ? 1 : 0;
            }
        }

        //draw rectangle framing public comments
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 40 - ($lineCount * 13));

        //draw public comments and their header
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 14);
        $page->drawText(__('Special instructions :'), 30, $this->y - 20, 'UTF-8');

        $this->_setFontRegular($page, 12);

        foreach ($comments as $i => $line){
            $lines = $this->splitTextToSize($line, $page->getFont(), 12, 485);
            foreach($lines as $j => $text){
                $page->drawText($text, 60, ($this->y - 40 - ($i *13) - ($j * 13)), 'UTF-8');
            }
        }

        $this->y -= 60 + ($lineCount * 13);
    }

    public function insertAdditionnal($page, $order)
    {
        //nothing, used for drop ship
    }

    protected function drawSupplierInstruction($page, $order)
    {
        $instruction = $order->getSupplier()->getsup_shipping_instructions();

        if (!$instruction)
            return $this;

        $instruction = explode("\r\n", $instruction);
        $lineCount = count($instruction) + 1;
        foreach ($instruction as $line){
            $linesSplitToSize = $this->splitTextToSize($line, $page->getFont(), 12, 485);
            foreach($linesSplitToSize as $index => $text)
            {
                $lineCount += $index > 0 ? 1 : 0;
            }
        }

        //draw rectangle framing public comments
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 40 - ($lineCount * 13));

        //draw public comments and their header
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 14);
        $page->drawText(__('Supplier instructions :'), 30, $this->y - 20, 'UTF-8');

        $this->_setFontRegular($page, 12);

        foreach ($instruction as $i => $line){
            $lines = $this->splitTextToSize($line, $page->getFont(), 12, 485);
            foreach($lines as $j => $text){
                $page->drawText($text, 60, ($this->y - 40 - ($i *13) - ($j * 13)), 'UTF-8');
            }
        }

        $this->y -= 60 + ($lineCount * 13);
    }

    public function getPdfObject()
    {
        return $this->_getPdf();
    }

    public function setFontBold($page, $size)
    {
        $this->_setFontBold($page, $size);
        return $this;
    }

    public function setFontRegular($page, $size)
    {
        $this->_setFontRegular($page, $size);
        return $this;
    }

    public function setCustomCBMTotal($totalCbm1) {
        $this->cbmTotal[] = $totalCbm1;
    }

    public function getProductImage($item)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $image = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getId());       
        $fileSystem = $objectManager->create('\Magento\Framework\Filesystem'); 
        $mediaDirectory = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        if (!is_null($image)) {
            try {
                $imagePath = '/catalog/product/' . $image->getSmallImage();
                if ($mediaDirectory->isFile($imagePath)) {
                    $pdfImage = $mediaDirectory->getAbsolutePath("catalog/product".$image->getSmallImage());
                    $image =  \Zend_Pdf_Image::imageWithPath($pdfImage);

                    return $image;
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }
    
    public function getShippingAddress()
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$bConfig = $objectManager->get('BoostMyShop\Supplier\Model\Config');
    	
        return $bConfig->getSetting('pdf/shipping_address', 1);
    }
}
