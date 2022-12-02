<?php

namespace BoostMyShop\OrderPreparation\Model\Batch\Type;

use Magento\Backend\App\Area\FrontNameResolver;

abstract class AbstactPdf extends \Magento\Sales\Model\Order\Pdf\AbstractPdf
{
    protected $_storeManager;
    protected $_localeResolver;
    protected $_config;
    protected $_barcode;
    protected $_batchHelper;
    protected $_opProduct;
    protected $_pickingList;
    protected $_pdfs = [];
    protected $_width = null;
    protected $_height = null;
    protected $_pageSize = null;
    protected $x;

    protected $_objectManager;
    protected $_configScope;

    const LINE_HEIGHT = 15;

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
        \BoostMyShop\OrderPreparation\Model\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \BoostMyShop\OrderPreparation\Model\Pdf\Barcode $barcode,
        \BoostMyShop\OrderPreparation\Model\BatchHelper $batchHelper,
        \BoostMyShop\OrderPreparation\Model\Product $opProduct,
        \BoostMyShop\OrderPreparation\Model\Batch\Type\PickingList $pickingList,
        array $data = []
    ) {
        $this->_config = $config;
        $this->_storeManager = $storeManager;
        $this->_localeResolver = $localeResolver;
        $this->_barcode = $barcode;
        $this->_batchHelper = $batchHelper;
        $this->_opProduct = $opProduct;
        $this->_pickingList = $pickingList;
        parent::__construct($paymentData, $string, $scopeConfig, $filesystem, $pdfConfig, $pdfTotalFactory, $pdfItemsFactory, $localeDate, $inlineTranslation, $addressRenderer, $data);
    }

    public function getPdf($batches = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('order');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        $this->_pageSize = $this->_config->getBatchPdfFormat();
        $this->_width = explode(":",$this->_pageSize)[0];
        $this->_height = explode(":",$this->_pageSize)[1];

        foreach ($batches as $batch)
        {
            $page = $this->newPage();

            $font = $this->_setFontBold($page, 25);
            $heading = __('Batch')." #".$batch->getbob_label();
            $page->drawText(trim(strip_tags($heading)), $this->getAlignCenter($heading, 0, $this->_width, $font, 25), $this->y, 'UTF-8');
            $this->y -= 30;

            $top = $this->insertBarcode($page, $batch->getbob_label());

            //Extra space after barcode
            $this->y -= 20;

            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $font = $this->_setFontRegular($page, 14);

            $text = __('Date')." : ".$batch->getbob_created_at();
            $page->drawText(trim(strip_tags($text)),20 , $this->y, 'UTF-8');
            $this->y -= 15;

            $allTypes = $this->_batchHelper->getTypes();
            $text = __('Type')." : ". (isset($allTypes[$batch->getbob_type()])?$allTypes[$batch->getbob_type()]:$batch->getbob_type());
            foreach ($this->string->split($text, 65, true, true) as $_value) {
                $page->drawText(trim(strip_tags($_value)), 20, $this->y,  'UTF-8');
                $this->y -= 15;
            }

            $allCarriers = $this->_batchHelper->getAllAcrriers();
            $text = __('Carrier')." : ". (isset($allCarriers[$batch->getbob_carrier()])?$allCarriers[$batch->getbob_carrier()]:$batch->getbob_carrier());
            foreach ($this->string->split($text, 65, true, true) as $_value) {
                $page->drawText(trim(strip_tags($_value)), 20, $this->y,  'UTF-8');
                $this->y -= 15;
            }
            $this->y -= 15;

            $text = __('Order count')." : ".$batch->getbob_order_count();
            $page->drawText(trim(strip_tags($text)),20 , $this->y, 'UTF-8');
            $this->y -= 40;
            if ($this->_pageSize == \Zend_Pdf_Page::SIZE_A4)
                $this->_pickingList->insertGlobalData($this->_getPdf(), $page, $batch);
            else {
                $this->insertTypeData($page, $batch);
                $this->mergePdfs($this->_pdfs);
            }

            $this->insertLastBatchPage($batch);

            $this->addPageNumbering();
        }

        $this->_afterGetPdf();

        return $this->_getPdf();
    }

    public function newPage(array $settings = [])
    {
        $pageSize = $this->_pageSize ? $this->_pageSize : \Zend_Pdf_Page::SIZE_A4;
        $this->x = $this->_width;
        $this->y = $this->_height-20;
        $page = $this->_getPdf()->newPage($pageSize);
        $this->_getPdf()->pages[] = $page;

        if($pageSize !==\Zend_Pdf_Page::SIZE_A4){
            $page->setFillColor(new \Zend_Pdf_Color_Rgb(1,1, 1));
            $page->setLineWidth(0.5);
            $page->drawRectangle(10, $this->_height-10, $this->_width-10, 10 );
        }

        $this->y -= 20;

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        return $page;
    }

    protected function insertBarcode(&$page, $barcodeNumber)
    {
        if ($this->y < 25) {
            $page = $this->newPage();
        }

        $barcodeImage = $this->_barcode->getZendPdfBarcodeImage($barcodeNumber);
        $width = 200;
        $height = 70;
        $x = ($this->_width - $width) / 2;
        $y = $this->y;
        $page->drawImage($barcodeImage, $x, $y - $height, $x + $width, $y);
        $this->y -= $height + 30;

    }

    protected function insertLastBatchPage($batch)
    {
        $page = $this->newPage();
        $this->y -= 30;
        $font = $this->_setFontBold($page, 25);
        $heading = __('Batch')." #".$batch->getbob_label();
        $page->drawText(trim(strip_tags($heading)), $this->getAlignCenter($heading, 0, $this->_width, $font, 25), $this->y, 'UTF-8');
        $this->y -= 30;
        $text = __('-- END --');
        $page->drawText($text, $this->getAlignCenter($text, 0, $this->_width, $font, 25), $this->y, 'UTF-8');
    }

    protected function addPageNumbering()
    {
        $pageCount = sizeof($this->_getPdf()->pages);
        foreach($this->_getPdf()->pages AS $key => $page)
        {
            $font = $this->_setFontRegular($page, 10);
            $text = ($key + 1) . "/" . $pageCount;
            $page->drawText($text, $this->getAlignRight($text, 0, $this->_width, $font, 25), 15, 'UTF-8');
        }
    }

    protected function getErrorOrderPage($order)
    {
        $page = $this->newPage();
        $this->y -= 30;
        $font = $this->_setFontBold($page, 25);
        $heading = __('Order')." #".$order->getIncrementId();
        $page->drawText(trim(strip_tags($heading)), $this->getAlignCenter($heading, 0, $this->_width, $font, 25), $this->y, 'UTF-8');
        $this->y -= 20;
        $font = $this->_setFontBold($page, 12);
        $text = __('Customer : %1', $order->getCustomerName());
        $page->drawText($text, $this->getAlignCenter($text, 0, $this->_width, $font, 12), $this->y, 'UTF-8');
        $this->y -= 20;
        $text = __('Unable to generate shipping label');
        $page->drawText($text, $this->getAlignCenter($text, 0, $this->_width, $font, 12), $this->y, 'UTF-8');
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

    protected function cleanReference($reference)
    {
        $t = explode('_', $reference);
        if (isset($t[0]) && count($t) > 1)
            unset($t[0]);
        return implode('_', $t);
    }

    protected function mergePdfs($pdfs)
    {
        $mergedPdf = $this->_getPdf();

        foreach($pdfs as $pdf)
        {
            if($pdf != '') {
                $pdf1 = null;
                try{
                    $pdf1 = \Zend_Pdf::parse($pdf, 1);
                } catch(\Exception $ex)
                {
                    //nothing
                }
                if ($pdf1)
                {
                    foreach ($pdf1->pages as $template)
                        $mergedPdf->pages[] = clone $template;
                }
            }
        }

        return $mergedPdf;
    }

    protected abstract function insertTypeData(&$page, $batch);

    protected function getObjectManager($forceApplyAreaCode = false)
    {
        if (null == $this->_objectManager) {
            $area = $forceApplyAreaCode ? $forceApplyAreaCode: FrontNameResolver::AREA_CODE;
            $this->_configScope = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Config\ScopeInterface::class);
            $this->_configScope->setCurrentScope($area);
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }
        return $this->_objectManager;
    }
}

