<?php namespace BoostMyShop\OrderPreparation\Model\Pdf;

class PickingList extends \Magento\Sales\Model\Order\Pdf\AbstractPdf
{
    protected $_storeManager;
    protected $_messageManager;
    protected $_localeResolver;
    protected $_config;
    protected $_product;
    protected $_productFactory;
    protected $_imageHelper;
    protected $_barcode;
    protected $_orderItemFactory;
    protected $_displaySummary;
    protected $_preparationRegistry = true;
    protected $_eventManager;
    protected $_viewGiftMessage;
    protected $_directoryList;
    protected $_warehouses;
    protected $_userFactory;
    protected $_scopeConfig;

    protected $_footerText;

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
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        \BoostMyShop\OrderPreparation\Model\ProductFactory $product,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \BoostMyShop\OrderPreparation\Model\Pdf\Barcode $barcode,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
        \BoostMyShop\OrderPreparation\Model\Config\Source\Warehouses $warehouses,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\GiftMessage\Model\MessageFactory $viewGiftMessage,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,

        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        $this->_messageManager = $messageManager;
        $this->_localeResolver = $localeResolver;
        $this->_config = $config;
        $this->_product = $product;
        $this->_productFactory = $productFactory;
        $this->_imageHelper = $imageHelper;
        $this->_barcode = $barcode;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_viewGiftMessage = $viewGiftMessage;        
        $this->_directoryList = $directoryList;
        $this->_displaySummary = $this->_config->includeGlobalPickingList();
        $this->_warehouses = $warehouses;
        $this->_userFactory = $userFactory;
        $this->_scopeConfig = $scopeConfig;

        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
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
        if ($this->_config->getPdfPickingLayout() != 'small') {
            $this->y -= 20;
            return;
        }

        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;

        //columns headers
        $lines[0][] = ['text' => __('Qty'), 'feed' => 100, 'align' => 'center'];
        $lines[0][] = ['text' => __('Location'), 'feed' => 130, 'align' => 'left'];
        $lines[0][] = ['text' => __('SKU'), 'feed' => 200, 'align' => 'left'];
        $lines[0][] = ['text' => __('Product'), 'feed' => 350, 'align' => 'left'];

        //raise event to allow other modules to modify the content of the headers...
        $obj = new \Magento\Framework\DataObject();
        $obj->setLines($lines);
        $this->_eventManager->dispatch('bms_orderpreparation_pickinglist_draw_header', ['lines' => $obj]);
        $lines = $obj->getLines();

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
        $this->_setFontBold($style, 10);

        if (!$this->_displaySummary && !$this->_config->pickingListOnePagePerOrder()) {
            throw new \Exception(
                'The PDF is empty because both "Include single order picklist" and "Include global picklist" options are set to "No" in the configuration.'
            );
        }

        if ($this->_config->getPickingPerBins())
        {
            $this->addOrdersSummaryPage($orders);
        }


        if ($this->_displaySummary) {
            $this->addSummaryPage($orders);
        }

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
                    $item->setMpn($this->_product->create()->getMpn($item->getproduct_id()));
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
                $this->insertLogo($page, $orderInProgress->getStore());
                $this->insertBarcode($page, $orderInProgress);
                $this->drawOrderInformation($page, $orderInProgress);
                $this->drawAddresses($page, $orderInProgress->getOrder());
                $this->addGiftMessage($page, $orderInProgress->getOrder());

                $this->_eventManager->dispatch(
                    'bms_orderpreparation_picking_list_before_print_products',
                    [
                        'page' => $page,
                        'pickinglist' => $this,
                        'orderId' => $orderInProgress->getOrder()->getId(),
                        'items' => $tItems
                    ]
                );

                /* Add body */
                $this->_drawHeader($page);

                foreach ($tItems as $item) {
                    $this->_drawProduct($item, $page, true);
                    if ($this->y < 50) {
                        $page = $this->newPage();
                        $this->drawOrderInformation($page, $orderInProgress);
                        $this->y -= 30;
                    }
                    $page = end($pdf->pages);
                }
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    protected function insertLogo(&$page, $store = null)
    {
        $previousY = $this->y;
        parent::insertLogo($page, $store);
        if ($previousY == $this->y) {
            $this->y -= 50;
        }
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
        $this->insertLogo($page, $storeId);

        $this->_setFontBold($page, 18);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 15;
        $page->drawText(__('Global Picking Sheet'), 30, $this->y, 'UTF-8');
        $this->y -= 40;

        $this->_drawHeader($page);
        foreach ($items as $item) {
            $this->_drawProduct($item, $page, false);
            if ($this->y < 50) {
                $page = $this->newPage();
            }
        }
    }

    public function addOrdersSummaryPage($ordersInProgress)
    {
        $storeId = false;
        foreach ($ordersInProgress as $orderInProgress) {
            if (!$storeId)
                $storeId = $orderInProgress->getOrder()->getStoreId();
        }

        $page = $this->newPage();
        $this->insertLogo($page, $storeId);

        $this->_setFontBold($page, 18);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 15;
        $page->drawText(__('Orders'), 30, $this->y, 'UTF-8');
        $this->y -= 40;

        $lines = [];
        $this->_setFontRegular($page, 12);
        $binId = 1;
        foreach($ordersInProgress as $orderInProgress)
        {
            $orderInProgress->setBinId(sprintf('%02d', $binId++));

            $line = [];
            $line[] = ['text' => $orderInProgress->getBinId(), 'feed' => 50, 'align' => 'center', 'circle' => 1];
            $line[] = ['text' => $orderInProgress->getIncrementId(), 'feed' => 90, 'align' => 'left'];
            $line[] = ['text' => $orderInProgress->getCustomerName(), 'feed' => 200, 'align' => 'left'];

            $lines[] = $line;

        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $lineBlock = ['lines' => $lines, 'height' => 25];
        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => false]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

    }

    public function sortPickingList($items)
    {
        $sortMode = $this->_config->getSetting('picking/sort_mode');

        switch($sortMode)
        {
            case 'name':
                usort($items, function ($a, $b) {
                    return strcmp($a->getName(), $b->getName());
                });
                break;
            case 'sku':
                usort($items, function ($a, $b) {
                    return strcmp($a->getSku(), $b->getSku());
                });
                break;
            case 'location_name':
            default:
                usort($items, function ($a, $b) {
                    return strcmp($a->getLocation().$a->getName(), $b->getLocation().$b->getName());
                });
                break;
        }

        return $items;
    }

    /**
     * @param $page
     * @param $barcodeNumber
     */
    protected function insertBarcode($page, $orderInProgress)
    {
        //add barcode
        $barcodeImage = $this->_barcode->getZendPdfBarcodeImage($orderInProgress->getIncrementId());
        $x = 420;
        $y = 820;
        $width = 160;
        $height = 50;
        $page->drawImage($barcodeImage, $x, $y - $height, $x + $width, $y);

        //add bin
        if ($this->_config->getPickingPerBins())
        {
            $this->_setFontBold($page, 36);
            $page->drawCircle($x - 23, $y - $height + 30, 20, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
            $page->drawText($orderInProgress->getBinId(), $x - 40,  $y - $height + 20, 'UTF-8');
        }

        return $this;
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

        //add footer
        /*
        if (!$this->_footerText)
        {
            $warehouses = $this->_warehouses->toOptionArray();
            $warehouseName =  isset($warehouses[$this->_preparationRegistry->getCurrentWarehouseId()]) ? $warehouses[$this->_preparationRegistry->getCurrentWarehouseId()] : 'Default';
            $operatorName = $this->_userFactory->create()->load($this->_preparationRegistry->getCurrentOperatorId())->getusername();
            $this->_footerText = date('Y-m-d H:i:s').' - '.$operatorName;
        }
        $this->_setFontRegular($page, 10);
        $page->drawText($this->_footerText, 20, 20, 'UTF-8');
        */

        return $page;
    }

    /**
     * @param $item
     * @param $page
     * @param $order
     */
    protected function _drawProduct($item, $page, $drawParent = false)
    {
        $separator = '';
        if ($item->getBarcode()) {
            $separator = ' / ';
        }

        //raise event to allow other modules to modify the content of the headers...
        $this->_setFontRegular($page, 10);  //required if event draw blocks itself
        $obj = new \Magento\Framework\DataObject();
        $obj->setItem($item);
        $obj->setPage($page);
        $obj->setDrawParent($drawParent);
        $obj->setPdfClass($this);
        $this->_eventManager->dispatch('bms_orderpreparation_pickinglist_draw_product', ['obj' => $obj]);
        if ($obj->getSkipRegularRendering())
            return;

        switch ($this->_config->getPdfPickingLayout()) {
            case 'small':

                if ($drawParent && $item->getParentName()) {
                    $this->y -= 3;
                    $page->drawText($item->getParentName(), 100, $this->y, 'UTF-8');
                    $this->y -= 5;
                    $page->drawLine(100, $this->y, 470, $this->y);
                    $this->y -= 13;
                }

                $this->_setFontRegular($page, 10);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

                //columns headers
                $square = false;
                if($item->getipi_qty() > 1)
                    $square = true;

                $firstLine = $this->y;
                $lines[0][] = ['text' => $item->getipi_qty(), 'feed' => 100, 'align' => 'center', "square" => $square];
                //$lines[0][] = ['text' => $item->getLocation(), 'feed' => 130, 'align' => 'left'];
                //$lines[0][] = ['text' => $item->getSku().$separator.$item->getBarcode(), 'feed' => 200, 'align' => 'left'];
                //$lines[0][] = ['text' => $item->getName(), 'feed' => 350, 'align' => 'left'];
                $lineBlock = ['lines' => $lines, 'height' => 5];

                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

                $textLocation = wordwrap($item->getLocation(), 8, "\n",true);
                $i =0;
                $this->y += 5;
                foreach(explode("\n", $textLocation) as $location){
                    if (!ctype_space($location) && $location !== '') {
                        $page->drawText(strip_tags(ltrim($location)), 130,$this->y,'UTF-8');
                        $this->y -=10;
                        $i++;
                    }
                }
                $this->y += (10*$i);

                //sku
                $this->y += 10;
                $skuLines = [(strlen($item->getSku()) <= 23 ? $item->getSku() : substr($item->getSku(), 0, 23))];
                if ($item->getBarcode() && $item->getBarcode() != $item->getSku())
                    $skuLines[] = $item->getBarcode();
                if ($item->getMpn())
                    $skuLines[] = $item->getMpn();
                foreach ($skuLines as $nameLine) {
                    $page->drawText($nameLine, 200, $this->y - 15 + 5, 'UTF-8');
                    $this->y -= 10;
                }
                $this->y += (10 * count($skuLines));

                //product name
                $nameLines = $this->splitTextToSize($item->getName(), $page->getFont(), 12, 200);
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
                    $page->drawText($nameLine, 350, $this->y - 15 + 5, 'UTF-8');
                    $this->y -= 10;
                }

                //fix problem if product name has only one line but we have a barcode
                if ((count($nameLines) == 1) && count($skuLines) > 1)
                    $this->y -= 10 * count($skuLines);

                //add order bins if enabled
		        $binHeight = 20;
                if ($this->_config->getPickingPerBins() && $item->getBinIds())
                {
                    $this->y -= 20;
                    $x = 200;
                    $shownBinCount = 0;
                    foreach($item->getBinIds() as $binId => $qty)
                    {
                        $page->drawCircle($x + 4, $this->y + 3, 7, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                        $page->drawText($binId.'  x'.$qty, $x, $this->y, 'UTF-8');
                        $x += 50;

                        $shownBinCount++;
                        if ($shownBinCount % 7 == 0)
                        {
                            $x = 200;
                            $this->y -= 20;
			                $binHeight += 20;
                        }
			
                    }
                    $this->y -= 20;
                }

                //add separation line
                $this->y = $firstLine;
                $this->y -= (10*max(count($skuLines),count(explode("\n", $textLocation)),count($nameLines))) + $binHeight;
                $page->drawLine(25, $this->y, 570, $this->y);
                $this->y -= 15;

                break;
            case 'large':
                $product = $this->_productFactory->create()->load($item->getproduct_id());
                $imageHelper = $this->_imageHelper->init($product, 'product_listing_thumbnail');
                $imageUrl = $imageHelper->getUrl();
                if ($imageUrl)
                {
                    if (strpos($imageUrl, 'pub/') > 0)
                        $imagePath = substr($imageUrl, strpos($imageUrl, 'pub/'));
                    else
                        $imagePath = 'pub/'.substr($imageUrl, strpos($imageUrl, 'media/'));
                    $imagePath = $this->_directoryList->getRoot().'/'.$imagePath;

                    try {
                        $image = \Zend_Pdf_Image::imageWithPath($imagePath);
                        $page->drawImage($image, 50, $this->y - 15, 50 + 40, $this->y - 15 + 40);
                    } catch (\Exception $ex) {
                        //nothing
                    }
                }

                $this->_setFontRegular($page, 24);
                $page->drawText($item->getipi_qty().'x', 120, $this->y, 'UTF-8');

                $this->_setFontRegular($page, 18);
                $page->drawText($item->getLocation(), 200, $this->y, 'UTF-8');

                $this->_setFontRegular($page, 12);
                $page->drawText($item->getSku().$separator.$item->getBarcode(), 300, $this->y + 5, 'UTF-8');

                $finalOffset = 50;

                $nameLines = $this->splitTextToSize($item->getName(), $page->getFont(), 12, 200);
                if ($item->getMpn())
                    $nameLines[] = 'Mpn : '.$item->getMpn();
                foreach ($nameLines as $nameLine) {
                    $page->drawText($nameLine, 300, $this->y - 15 + 5, 'UTF-8');
                    $this->y -= 15;
                    $finalOffset -= 10;
                }

                if ($this->_config->displayCustomOptionsOnPicking() && $item->getOptions()) {
                    foreach ($item->getOptions() as $option) {
                        $page->drawText($option, 300, $this->y - 5, 'UTF-8');
                        $this->y -= 20;
                        $finalOffset -= 5;
                    }
                }

                if ($item->getConfigurableOptions()) {
                    foreach ($item->getConfigurableOptions() as $option) {
                        $page->drawText($option, 300, $this->y - 5, 'UTF-8');
                        $this->y -= 20;
                        $finalOffset -= 5;
                    }
                }

                $this->y -= $finalOffset;

                //add order bins if enabled
                $this->_setFontRegular($page, 18);
                if ($this->_config->getPickingPerBins() && $item->getBinIds())
                {
                    $x = 100;
                    foreach($item->getBinIds() as $binId => $qty)
                    {
                        $page->drawCircle($x + 7, $this->y + 4, 11, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                        $page->drawText($binId.'  x'.$qty, $x, $this->y, 'UTF-8');
                        $x += 80;
                    }
                    $this->y -= 25;
                }

                $this->y -= 12;
                $page->drawLine(25, $this->y + 26, 570, $this->y + 26);
                $this->y -= 5;

                break;
        }
    }

    public function getConfigurableOptionsAsText($item)
    {
        $txt = array();

        if ($item->getOrderItem()->getparent_item_id()) {
            $parentItem = $this->_orderItemFactory->create()->load($item->getOrderItem()->getparent_item_id());
            $options = $parentItem->getProductOptions();
            if (isset($options['attributes_info']) && is_array($options['attributes_info'])) {
                foreach ($options['attributes_info'] as $info) {
                    $txt[] = $info['label'].': '.$info['value'];
                }
            }
        }

        return $txt;
    }

    protected function getOptionsAsText($item)
    {
        $txt = [];
        $options = $item->getOrderItem()->getProductOptions();

        if (isset($options['options']) && is_array($options['options']) && count($options['options']) > 0) {
            foreach ($options['options'] as $option) {
                if (isset($option['label'])) {
                    if (isset ($option['print_value'])) {
                        $txt[] = $option['label'].' : '.$option['print_value'];
                    } elseif (isset($option['value'])) {
                        $txt[] = $option['label'].' : '.$option['value'];
                    }
                }
            }
        } else {
            //try with parent
            if ($item->getOrderItem()->getparent_item_id()) {
                $parentItem = $this->_orderItemFactory->create()->load($item->getOrderItem()->getparent_item_id());
                $options = $parentItem->getProductOptions();
                if (isset($options['options']) && is_array($options['options']) && count($options['options']) > 0) {
                    foreach ($options['options'] as $option) {
                        if (isset($option['label'])) {
                            if (isset($option['print_value'])) {
                                $txt[] = $option['label'].' : '.$option['print_value'];
                            } elseif (isset($option['value'])) {
                                $txt[] = $option['label'].' : '.$option['value'];
                            }
                        }
                    }
                }
            } else {
                return false;
            }
        }

        return $txt;
    }

    protected function getOptionsKey($item)
    {
        $txt = [];
        $options = $item->getOrderItem()->getProductOptions();

        if (isset($options['options']) && is_array($options['options']) && count($options['options']) > 0) {
            foreach ($options['options'] as $option) {
                if (isset($option['option_id']) && isset($option['option_value'])) {
                    $txt[] = $option['option_id'].' : '.$option['option_value'];
                }elseif (isset($option['label']) && isset($option['value'])) {
                    $txt[] = $option['label'].' : '.$option['value'];
                }
            }
        }

        return implode('_', $txt);
    }

    /**
     * @param $page
     * @param $order
     */
    protected function insertCosts($page, $order)
    {
        $costs = ['Shipping' => $order->getpo_shipping_cost(), 'Additionnal' => $order->getpo_additionnal_cost()];
        foreach ($costs as $label => $value) {
            if ($value > 0) {
                $lines = [];
                $this->_setFontRegular($page, 10);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
                $lines[0][] = ['text' => $label, 'feed' => 200, 'align' => 'left'];
                $lines[0][] = ['text' => $order->getCurrency()->format($value, [], false), 'feed' => 480, 'align' => 'right'];
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
        $totals = [];
        $totals[] = ['label' => __('Subtotal'), 'value' => $order->getPoSubtotal()];
        $totals[] = ['label' => __('Shipping & additionnal'), 'value' => $order->getPoShippingCost() + $order->getPoAdditionnalCost()];
        $totals[] = ['label' => __('Taxes'), 'value' => $order->getPoTax()];
        $totals[] = ['label' => __('Grand total'), 'value' => $order->getPoGrandtotal()];

        $page->drawLine(25, $this->y, 570, $this->y);
        $this->y -= 20;

        $this->_setFontBold($page, 18);

        foreach ($totals as $total) {
            $lines = [];
            $lines[0][] = ['text' => __($total['label']), 'font_size' => 14, 'feed' => 350, 'align' => 'left'];
            $lines[0][] = ['text' => $order->getCurrency()->format($total['value'], [], false), 'font_size' => 14, 'feed' => 550, 'align' => 'right'];
            $lineBlock = ['lines' => $lines, 'height' => 20];
            $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        }

        return $page;
    }

    /**
     * Insert gift message blocks
     *
     * @param $page
     * @param $order
     */

    protected function addGiftMessage($page, $order)
    {
        if ($order->getgift_message_id())
        {
            
            $giftMessages =  $this->_viewGiftMessage->create()->load($order->getgift_message_id());

            if (strlen(trim($giftMessages->getMessage())) < 3)
                return;

            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->setLineWidth(0.5);
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawLine(25, $this->y, 570, $this->y);
            $this->y -= 20;
            $page->drawText(__('Gift Message:'), 30, $this->y, 'UTF-8');

            $this->y -= 25;
            $page->drawText(__('From:'), 30, $this->y, 'UTF-8');
            $this->_setFontRegular($page, 12);
            $page->drawText($giftMessages->getSender(), 70, $this->y, 'UTF-8');
            $this->_setFontBold($page, 12);        
            $page->drawText(__('To:'), 285, $this->y, 'UTF-8');
            $this->_setFontRegular($page, 12);
            $page->drawText($giftMessages->getRecipient(), 308, $this->y, 'UTF-8');

            $this->y -= 25;
            $this->_setFontBold($page, 12);        
            $page->drawText(__('Message:'), 30, $this->y, 'UTF-8');
            $this->_setFontRegular($page, 12);
            

            $textGiftmsg = wordwrap($giftMessages->getMessage(), 92, "\n");
            foreach(explode("\n", $textGiftmsg) as $textLine){
              if ($giftMessages->getMessage()!=='') {
                $page->drawText(strip_tags(ltrim($textLine)), 120,$this->y, 'UTF-8');
                $this->y -=14;
              }
            }

            $this->y -= 20;        
            $page->drawLine(25, $this->y, 570, $this->y);
            $this->y -= 30;
        }

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
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 140);

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        $this->y -= 15;
        $page->drawText(__('Bill to :'), 30, $this->y, 'UTF-8');
        $page->drawText(__('Ship to :'), 300, $this->y, 'UTF-8');

        $this->_setFontRegular($page, 12);
        $billingAddress = $this->addressRenderer->format($order->getBillingAddress(), 'html');
        $billingAddress = str_replace("\n", "", $billingAddress);
        $billingAddress = str_replace("<br />", "<br/>", $billingAddress);
        $billingAddress = str_replace("&#039;", " ", $billingAddress);

        $i = 0;
        foreach (explode("<br/>", $billingAddress) as $line) {
            $line = str_replace(chr(13), "", $line);
            $line = strip_tags($line);
            if ($line) {
                $page->drawText($line, 60, $this->y - 20 - ($i * 13), 'UTF-8');
                $i++;
            }
        }

        if ($order->getShippingAddress()) {
            $shippingAddress = $this->addressRenderer->format($order->getShippingAddress(), 'html');
            $shippingAddress = str_replace("\n", "", $shippingAddress);
            $shippingAddress = str_replace("<br />", "<br/>", $shippingAddress);
            $shippingAddress = str_replace("&#039;", " ", $shippingAddress);
            $i = 0;
            foreach (explode("<br/>", $shippingAddress) as $line) {
                $line = str_replace(chr(13), "", $line);
                $line = strip_tags($line);
                if ($line) {
                    $page->drawText($line, 330, $this->y - 20 - ($i * 13), 'UTF-8');
                    $i++;
                }
            }
        }

        $this->y -= 140;
    }

    protected function drawOrderInformation($page, $orderInProgress)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 130);

        $this->_setFontBold($page, 14);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__('Order # %1', $orderInProgress->getOrder()->getIncrementId()), 30, $this->y - 20, 'UTF-8');

        $this->_setFontRegular($page, 12);
        $additionnalTxt = [];
        $additionnalTxt[] = __('Operator: %1', $orderInProgress->getOperatorName());
        $additionnalTxt[] = __('Shipping method: %1', $orderInProgress->getOrder()->getShippingDescription());
        $additionnalTxt[] = __('Date: %1', date('Y-m-d H:i:s'));
        $additionnalTxt[] = __('Total products: %1', $orderInProgress->getProductsCount());
        $additionnalTxt[] = __('Total weight: %1', $orderInProgress->getip_total_weight());
        if ($this->_config->getVolumeAttribute())
            $additionnalTxt[] = __('Total volume: %1', $orderInProgress->getip_total_volume());

        //raise event to allow other modules to add additional text...
        $obj = new \Magento\Framework\DataObject();
        $obj->setLines($additionnalTxt);
        $this->_eventManager->dispatch('bms_orderpreparation_pickinglist_header_additional_text', ['in_progress' => $orderInProgress, 'obj' => $obj]);
        $additionnalTxt = $obj->getLines();

        $i = 0;
        foreach ($additionnalTxt as $txt) {
            $page->drawText($txt, 60, $this->y - 40 - ($i * 13), 'UTF-8');
            $i++;
        }

        $this->y -= 140;
    }

    protected function drawPublicComments($page, $order)
    {
        $comments = $order->getpo_public_comments();

        if (!$comments) {
            return $this;
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 50);

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 14);
        $page->drawText(__('Special instructions :'), 30, $this->y - 20, 'UTF-8');

        $this->_setFontRegular($page, 12);
        $page->drawText($comments, 60, $this->y - 40, 'UTF-8');

        $this->y -= 60;
    }

    public function splitTextToSize($text, $font, $fontSize, $maxWidth)
    {
        $textSize = $this->widthForStringUsingFontSize($text, $font, $fontSize);
        $lines = [];

        $textLines = explode("<br>", $text);
        foreach($textLines as $line) {
            if ($textSize > $maxWidth) {
                $words = explode(' ', $line);
                $currentLine = '';
                foreach ($words as $word) {
                    if ($this->widthForStringUsingFontSize($currentLine . $word, $font, $fontSize) < $maxWidth) {
                        $currentLine .= $word . ' ';
                    } else {
                        $lines[] = $currentLine;
                        $currentLine = $word . ' ';
                    }
                }

                if ($currentLine != '') {
                    $lines[] = $currentLine;
                }
            } else {
                $lines[] = $line;
            }
        }
        return $lines;
    }

    public function displaySummary($value)
    {
        $this->_displaySummary = $value;
        return $this;
    }

    public static function sortPerLocation($a, $b)
    {
        if ($a->getLocation() > $b->getLocation()) {
            return $a;
        } else {
            return $b;
        }
    }

    /**
     * Return parent name if parent is a bundle
     *
     * @param $item
     */
    public function getParentName($item)
    {
        if ($this->_config->getGroupBundleItems()) {
            if ($item->getParentItem() && $item->getParentItem()->getproduct_type() == 'bundle') {
                return $item->getParentItem()->getName();
            }
        }
    }

    public function drawLineBlocks(\Zend_Pdf_Page $page, array $draw, array $pageSettings = [])
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We don\'t recognize the draw line data. Please define the "lines" array.')
                );
            }
            $lines = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = [$column['text']];
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

//            if ($this->y - $itemsProp['shift'] < 15) {
//                $page = $this->newPage($pageSettings);
//                $this->_setFontRegular($page, 12);
//            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $font = $this->setFont($page, $column);
                    $fontSize = isset($column['font_size'])?$column['font_size']:12;

                    $square = false;
                    if (isset($column['square'])) {
                        if($column['square'])
                            $square = true;
                    }

                    $circle = false;
                    if (isset($column['circle'])) {
                        if($column['circle'])
                            $circle = true;
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = [$column['text']];
                    }

                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    $x1  = $x2 = $y1 = $y2 = 0;
                    $y1 = $this->y+15-$lineSpacing;
                    foreach ($column['text'] as $part) {

                        if ($this->y - $lineSpacing < 15) {
                            $page = $this->newPage($pageSettings);
                            $fontSize = isset($column['font_size']) ? $column['font_size'] : 12;
                            $this->_setFontRegular($page, $fontSize);
                        }

                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                } else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                            default:
                                break;
                        }

                        if($x1 < $feed) {
                            $x1 = $feed;
                        }
                        if($x2 < $x1+10)
                        {
                            $x2 = $x1+10;
                        }

                        $page->drawText($part, $feed, $this->y - $top, 'UTF-8');
                        $top += $lineSpacing;
                    }


                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;

                    if($square) {
                        $y2 = $this->y - $top;
                        $x1 -= 5;
                        $x2 += 5;
                        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
                        $page->setLineWidth(1.5);
                        $page->drawLine($x1, $y1, $x2, $y1);
                        $page->drawLine($x2, $y1, $x2, $y2);
                        $page->drawLine($x2, $y2, $x1, $y2);
                        $page->drawLine($x1, $y2, $x1, $y1);
                        $page->setLineWidth(0.5);
                    }

                    if ($circle)
                    {
                        $page->drawCircle(55, $this->y + 3, 10, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                    }

                }
                $this->y -= $maxHeight;
            }
        }

        return $page;
    }

}
