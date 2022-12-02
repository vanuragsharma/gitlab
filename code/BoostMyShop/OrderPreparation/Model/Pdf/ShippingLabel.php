<?php

namespace BoostMyShop\OrderPreparation\Model\Pdf;


use Symfony\Component\Config\Definition\Exception\Exception;

class ShippingLabel extends \Magento\Sales\Model\Order\Pdf\AbstractPdf
{

    protected $_storeManager;
    protected $_messageManager;
    protected $_localeResolver;
    protected $_config;
    protected $_orderItemFactory;
    protected $_width = null;
    protected $_height = null;
    const LINE_HEIGHT = 15;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;


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
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_localeResolver = $localeResolver;
        $this->_config = $config;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_countryFactory = $countryFactory;

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
     * @param array|Collection
     * @return \Zend_Pdf
     */
    public function getPdf($orders = [])
    {
        $this->_beforeGetPdf();

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        $this->_width = $this->cm2point($this->_config->getSetting('addresslabel/label_width'));
        $this->_height = $this->cm2point($this->_config->getSetting('addresslabel/label_height'));
        foreach ($orders as $orderInProgress) {

            $page = $this->newPage(["page_size"=>"".(int)$this->_width.":".(int)$this->_height.":"]);
            $this->y = $this->_height;
            $this->shippingLabel($page, $orderInProgress);

        }
        
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * @param $page
     * @param $order
     */
    protected function shippingLabel($page, $order)
    {
        $textSize = $this->_config->getSetting('addresslabel/text_size');
        $topMargin = $this->cm2point($this->_config->getSetting('addresslabel/top_margin'));
        $leftMargin = $this->cm2point($this->_config->getSetting('addresslabel/left_margin'));


        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
        );

        $page->setFont($font, $textSize);
        $this->_setFontBold($page, $textSize);
                
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        $addressSelection = $order->getOrder()->getShippingAddress() ? $order->getOrder()->getShippingAddress() : $order->getOrder()->getBillingAddress();

        if ($addressSelection) {
            if($order->getCarrierTemplate()->getCustomValue('label_configuration')){
                $addressToDraw = $this->labelFormat($addressSelection,$order);
            }else{
                $addressToDraw = $this->addressRenderer->format($addressSelection, 'html');
                $addressToDraw = html_entity_decode($addressToDraw, ENT_QUOTES, 'UTF-8');
                $addressToDraw = str_replace("\n", "", $addressToDraw);
                $addressToDraw = str_replace("<br />", "<br/>", $addressToDraw);
                $addressToDraw = str_replace(chr(13), "", $addressToDraw);
            }

            //Find phone number inside <href>
            preg_match('#<a href=\"(.+)\">(.+)</a>#', $addressToDraw, $results);
            $phoneNumber = (isset($results[2]) && $results[2])?$results[2]:'';

            //Remove <href> around phone number
            $pattern = array("/<a(.[^>])+>/", "/<\/a>/");
            $addressToDraw = preg_replace($pattern,$phoneNumber,$addressToDraw);
            $y = $this->y - $topMargin - self::LINE_HEIGHT;
            $x = $leftMargin;

            foreach (explode("<br/>", $addressToDraw) as $line) {
                if ($line) {
                    $page->drawText($line, $x, $y, 'UTF-8');
                    $y -= self::LINE_HEIGHT;
                }
            }
        }

        $page->setFont($font, ($textSize-2));
        $text = __('Order # %1', $order->getOrder()->getIncrementId());
        $page->drawText(
            $text,
            $this->getAlignRight($text, 0, $this->_width, $font, ($textSize-2)),
            10,
            'UTF-8'
        );

        return $this;
    }


    protected function cm2point($cm)
    {
        $inches = $cm/2.54;
        $points = $inches*72;
        return $points;
    }

    public function labelFormat($addressSelection,$order)
    {
        $streetArray = $addressSelection->getstreet();
        $addressToDraw = $order->getCarrierTemplate()->getCustomValue('label_configuration');
        preg_match_all('/{(.*?)}/', $addressToDraw, $matches);
        foreach ($addressSelection->getData() as $key => $value) {
            if($key == 'country_id'){
                $country = $this->_countryFactory->create()->loadByCode($value)->getName();
                $addressToDraw = str_replace('{country}',$country,$addressToDraw);
            }

            if(in_array('{'.$key.'}',$matches[0]))
                $addressToDraw = str_replace('{'.$key.'}',$value,$addressToDraw);
        }

        for($i=0;$i<count($streetArray); $i++){
            $y= $i+1;
            $str = '{street'.$y.'}';
            $addressToDraw = str_replace($str,$streetArray[$i],$addressToDraw);
        }

        //remove street1 ,street2.. if not in code
        $addressToDraw = preg_replace('/{(street.*?)}/',"",$addressToDraw);
        //remove blank line
        $addressToDraw = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "<br/>", $addressToDraw);
        return $addressToDraw;
    }

}
