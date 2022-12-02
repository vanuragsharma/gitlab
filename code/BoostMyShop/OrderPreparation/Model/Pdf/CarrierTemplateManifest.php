<?php namespace BoostMyShop\OrderPreparation\Model\Pdf;

class CarrierTemplateManifest extends \Magento\Sales\Model\Order\Pdf\AbstractPdf
{
    protected $_storeManager;
    protected $_messageManager;
    protected $_localeResolver;
    protected $_config;
    protected $_displaySummary;
    protected $_preparationRegistry = true;
    protected $_eventManager;
    protected $_manifestFactory;
    protected $_warehouses;
    protected $trackNumbers = null;
    protected $_carrierTemplateCollectionFactory;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \BoostMyShop\OrderPreparation\Model\ManifestFactory $manifestFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \BoostMyShop\OrderPreparation\Model\Config\Source\Warehouses $warehouses,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate\CollectionFactory $carrierTemplateCollectionFactory,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        $this->_messageManager = $messageManager;
        $this->_localeResolver = $localeResolver;
        $this->_config = $config;
        $this->_manifestFactory = $manifestFactory;
        $this->_warehouses = $warehouses;
        $this->_carrierTemplateCollectionFactory = $carrierTemplateCollectionFactory;

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

        $shipments = $this->getShipments($this->getManifestId());
        $this->loadTrackingNumber($shipments);

        $page = $this->newPage();
        $this->insertSummary($page, $shipments);

        $this->_drawHeader($page);
        foreach($shipments as $shipment)
        {
            $this->drawShipment($page, $shipment);
            if ($this->y < 50) {
                $page = $this->newPage();
            }

        }

        $this->_afterGetPdf();
        return $pdf;
    }

    public function loadTrackingNumber($shipments)
    {
        $orderIds = [];
        if($this->trackNumbers == null) {
            $this->trackNumbers = [];
            foreach ($shipments as $shipment)
            {
                $order = $shipment->getOrder();
                if(!in_array($order->getId(),$orderIds))
                {
                    $tracksCollection = $order->getTracksCollection();
                    foreach ($tracksCollection->getItems() as $track)
                    {
                        if (!isset($this->trackNumbers[$track->getparent_id()]))
                            $this->trackNumbers[$track->getparent_id()] = [];
                        $this->trackNumbers[$track->getparent_id()] = $track->getTrackNumber();
                    }

                    $orderIds[] = $order->getId();
                }
            }
        }
        return $this->trackNumbers;
    }

    public function drawShipment($page, $shipment)
    {
        $this->_setFontRegular($page, 10);
        //columns headers
        $lines[0][] = ['text' => $this->removePrefix($shipment->getOrder()->getincrement_id()), 'feed' => 50, 'align' => 'left'];
        $lines[0][] = ['text' => $shipment->getincrement_id(), 'feed' => 180, 'align' => 'left'];
        $lines[0][] = ['text' => $shipment->gettotal_weight(), 'feed' => 280, 'align' => 'left'];
        $lines[0][] = ['text' => $this->removePrefix($shipment->getOrder()->getshipping_method()), 'feed' => 330, 'align' => 'left'];
        if(array_key_exists($shipment->getId(), $this->trackNumbers)) {
            foreach ($this->string->split($this->trackNumbers[$shipment->getId()], 25, true, true) as $count => $trackNumber)
                $lines[$count][] = ['text' => $trackNumber, 'feed' => 430, 'align' => 'left', 'height' => 10];
        }

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);

        $this->y -= 10;
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

        //columns headers
        $lines[0][] = ['text' => __('Order'), 'feed' => 50, 'align' => 'left'];
        $lines[0][] = ['text' => __('Shipment'), 'feed' => 180, 'align' => 'left'];
        $lines[0][] = ['text' => __('Weight'), 'feed' => 280, 'align' => 'left'];
        $lines[0][] = ['text' => __('Method'), 'feed' => 330, 'align' => 'left'];
        $lines[0][] = ['text' => __('Tracking'), 'feed' => 430, 'align' => 'left'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
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
        return $page;
    }

    public function getManifest(){
        return $this->_manifestFactory->create()->load($this->getManifestId());
    }

    protected function getShipments($manifestId)
    {
        $collection = $this->_manifestFactory->create()->getShipments($manifestId);

        return $collection;
    }

    protected function insertSummary($page, $shipments)
    {
        $manifest = $this->getManifest();
        $carrierTemplate = $this->loadCarrierTemplate($manifest->getbom_carrier(), $manifest->getbom_warehouse_id());
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 110);

        $this->_setFontBold($page, 14);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__('Shipping manifest for %1', $carrierTemplate->getct_name()), 30, $this->y - 20, 'UTF-8');

        $totalWeight = 0;
        foreach($shipments as $shipment)
            $totalWeight += $shipment->gettotal_weight();

        $this->_setFontRegular($page, 12);
        $freeText = explode('<br>', nl2br($carrierTemplate->getct_manifest_freetext(), FALSE));
        $count =0;
        foreach ($freeText as $text) {
            foreach ($this->string->split($text, 75, true, true) as $data) {
                if($count<5)
                    $page->drawText($data, 250, $this->y - 40 - ($count * 16), 'UTF-8');
                $count++;
            }
        }

        $additionnalTxt = [];
        $additionnalTxt[] = __('Warehouse: %1', $this->getWarehouseName($manifest->getbom_warehouse_id()));
        $additionnalTxt[] = __('Carrier: %1', $manifest->getbom_carrier());
        $additionnalTxt[] = __('Date: %1', $manifest->getbom_date());
        $additionnalTxt[] = __('Shipments: %1', $shipments->getSize());
        $additionnalTxt[] = __('Total weight: %1', $totalWeight);

        $i = 0;
        foreach ($additionnalTxt as $txt) {
            $page->drawText($txt, 60, $this->y - 40 - ($i * 16), 'UTF-8');
            $i++;
        }

        $this->y -= 120;
    }

    public function getWarehouseName($warehouseId)
    {
        $warehouses = $this->_warehouses->toOptionArray();
        return (isset($warehouses[$warehouseId]) ? $warehouses[$warehouseId] : 'Default');
    }

    public function loadCarrierTemplate($carrier, $warehouseId) {
        try {
            $template = $this->_carrierTemplateCollectionFactory->create()
                ->addActiveFilter()
                ->addShippingMethodFilter($carrier)
                ->addWarehouseFilter($warehouseId)
                ->getFirstItem();

            if($template)
                return $template;
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    public function removePrefix($value)
    {
        $t = explode('_', $value);
        if (count($t) > 1)
            unset($t[0]);
        return implode('_', $t);
    }
}
