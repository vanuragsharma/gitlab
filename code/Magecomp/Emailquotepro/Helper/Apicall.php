<?php
namespace Magecomp\Emailquotepro\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magecomp\Emailquotepro\Model\EmailproductquoteFactory;
use Magecomp\Emailquotepro\Model\Mail\TransportBuilder;
use Magento\Catalog\Helper\Image;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Quote\Model\Quote;


class Apicall extends AbstractHelper
{
    const XML_PATH_EMAIL_ADMIN_QUOTE_SENDER = 'emailquote/general/adminemailsender';
    const XML_PATH_EMAIL_ADMIN_QUOTE_NOTIFICATION = 'emailquote/general/adminemailtemplate';
    const XML_PATH_EMAIL_CUSTOMER_FEEDBACK_TEMPLATE = 'emailquote/general/customerFeedbacktemplate';
    const XML_PATH_EMAIL_ADMIN_NAME = 'Admin';
    const XML_PATH_EMAIL_ADMIN_EMAIL = 'emailquote/general/adminmailreceiver';


    protected $scopeConfig;
    protected $_modelStoreManagerInterface;
    protected $_helperImage;
    protected $_helperprice;
    protected $inlineTranslation;
    protected $transportBuilder;
    protected $_modelCart;
    protected $_logLoggerInterface;
    protected $_EmailproductquoteFactory;
    protected $checkoutSession;
    protected $_currencyFactory;
    protected $quote;

    public function __construct(
        Context $context,
        CurrencyFactory $currencyFactory,
        ScopeConfigInterface $configScopeConfigInterface,
        StoreManagerInterface $modelStoreManagerInterface,
        Image $helperImage,
        PricingHelper $helperprice,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        Cart $modelCart,
        LoggerInterface $logLoggerInterface,
        EmailproductquoteFactory $EmailproductquoteFactory,
        CheckoutSession $checkoutSession,
        Filesystem $filesystem,
        StringUtils $string,
        Quote $quote
    )
    {
        $this->scopeConfig = $configScopeConfigInterface;
        $this->_currencyFactory = $currencyFactory;
        $this->_modelStoreManagerInterface = $modelStoreManagerInterface;
        $this->_helperImage = $helperImage;
        $this->_helperprice = $helperprice;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->_modelCart = $modelCart;
        $this->_logLoggerInterface = $logLoggerInterface;
        $this->_EmailproductquoteFactory = $EmailproductquoteFactory;
        $this->checkoutSession = $checkoutSession;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->string = $string;
        $this->quote = $quote;
        parent::__construct($context);
    }
    public function sendEmail($customername,$customeremail,$telephone,$comment,$quoteid)
    {
        try {
            $quote=$this->quote->load($quoteid);
        
            $items = $quote->getAllVisibleItems();
            $imageHelper = $this->_helperImage;

            $currencyCode =  $this->_modelStoreManagerInterface->getStore()->getCurrentCurrencyCode();
            $currency = $this->_currencyFactory->create()->load($currencyCode);
            $currencySymbol = $currency->getCurrencySymbol();

            $qhtml = "<tr style='background-color:#e0e0e0'>";
            $qhtml .= "<th>Photo</th><th>Item</th><th>SKU</th><th>Qty</th><th class='right'>Total</th>";
            $qhtml .= "</tr>";

            $quoteId = $quote->getId();
            $shippingAmt = $quote->getShippingAddress()->getShippingAmount();
            $shippingTitle = $quote->getShippingAddress()->getShippingDescription();
            $taxrate = $quote->getShippingAddress()->getTaxAmount();


            foreach ($items as $item) {
                $img = $imageHelper->init($item->getProduct(), 'product_page_image_small')->getUrl();
                $qhtml .= "<tr>";
                $qhtml .= "<td style='text-align:center'><img src=" . $img . " alt=" . $item->getName() . " width='100' height='100' /></td>";
                $qhtml .= "<td style='text-align:center'>" . $item->getName();
                /* Bundle Product Option  start*/
                $products = $item->getProduct();
                if ($products->getTypeId() === 'bundle') {
                    $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

                    foreach ($options['bundle_options'] as $optionssub):
                        $qhtml .= "<br /><strong style='font-size:12px;'>";
                        $qhtml .= $optionssub['label'];
                        $qhtml .= "</strong>";
                        foreach ($optionssub['value'] as $selection) {
                            $formattedPriceOptions = $currencySymbol.$selection['price'];
                            $qhtml .= $selection['qty'] . " x " . $selection['title'] . " " . $formattedPriceOptions;
                        }
                    endforeach;
                }
                /* Bundle Product Option  end*/
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                if (array_key_exists("attributes_info", $options) && count($options['attributes_info']) >= 1) :
                    foreach ($options['attributes_info'] as $curopt) {
                        $qhtml .= "<br/><strong style='font-size:12px;'>";
                        $qhtml .= $curopt['label'] . " : " . $curopt['value'];
                        $qhtml .= "</strong>";
                    }
                endif;
                $qhtml .= "</td>";
                $qhtml .= "<td style='text-align:center'>" . $item->getSku() . "</td>";
                $qhtml .= "<td style='text-align:center'>" . $item->getQty() . "</td>";
                $qhtml .= "<td style='text-align:center'>" . $currencySymbol.number_format($item->getRowTotalInclTax(), 2, '.', '') . "</td>";
                $qhtml .= "</tr>";
            }

            $totals = $quote->getTotals();
            $grandtotal = $totals["grand_total"]->getValue();
            $formattedPrice = $currencySymbol.number_format($grandtotal, 2, '.', '');

            if ($shippingAmt > 0) {
                $qhtml .= "<tr>";
                $qhtml .= "<td valign='top' colspan='5'>";
                $qhtml .= "<p style='border:1px solid #E0E0E0; font-size:12px; line-height:16px; margin:0; padding:13px 18px; background:#F9F9F9; text-align:right;'><strong>" . $shippingTitle . " : " .$currencySymbol.number_format($shippingAmt, 2, '.', '') . "</strong></p></td>";
                $qhtml .= "</tr>";
            }
            if ($taxrate > 0) {
                $qhtml .= "<tr>";
                $qhtml .= "<td valign='top' colspan='5'>";
                $qhtml .= "<p style='border:1px solid #E0E0E0; font-size:12px; line-height:16px; margin:0; padding:13px 18px; background:#F9F9F9; text-align:right;'><strong> Tax Rate : " .$currencySymbol.number_format($taxrate, 2, '.', ''). "</strong></p></td>";
                $qhtml .= "</tr>";
            }

            $qhtml .= "<tr>";
            $qhtml .= "<td valign='top' colspan='5'>";
            $qhtml .= "<p style='border:1px solid #E0E0E0; font-size:12px; line-height:16px; margin:0; padding:13px 18px; background:#F9F9F9; text-align:right;'><strong>Grand Total : " . $formattedPrice . "</strong></p></td>";
            $qhtml .= "</tr>";

            // Send Mail To Admin For This
            $this->inlineTranslation->suspend();
            $storeScope = ScopeInterface::SCOPE_STORE;
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_ADMIN_QUOTE_NOTIFICATION, $storeScope))
                ->setTemplateOptions(
                    [
                        'area' => 'frontend',
                        'store' =>  $this->_modelStoreManagerInterface->getStore()->getId(),
                    ]
                )
                ->setTemplateVars([
                    'customerName' => $customername,
                    'customerEmail' => $customeremail,
                    'customerPhone' => $telephone,
                    'customerComment' => $comment,
                    'cartgrid' => $qhtml

                ])
                ->setFrom($this->scopeConfig->getValue(self::XML_PATH_EMAIL_ADMIN_QUOTE_SENDER, $storeScope))
                ->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_ADMIN_EMAIL, $storeScope))
                ->getTransport();
            try {
                 $transport->sendMessage();
            } catch (\Exception $e) {
                $this->_logLoggerInterface->debug($e->getMessage());
            }
            $this->inlineTranslation->resume();

            // Send Mail To Customer for Receving message For This
            $this->inlineTranslation->suspend();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_CUSTOMER_FEEDBACK_TEMPLATE, $storeScope))
                ->setTemplateOptions(
                    [
                        'area' => 'frontend',
                        'store' =>  $this->_modelStoreManagerInterface->getStore()->getId(),
                    ]
                )
                ->setTemplateVars([
                    'customerName' => $customername,
                    'customerEmail' => $customeremail,
                    'customerPhone' => $telephone,
                    'customerComment' => $comment,
                    'quoteid' => $quoteId,
                    'cartgrid' => $qhtml
                ])
                ->setFrom($this->scopeConfig->getValue(self::XML_PATH_EMAIL_ADMIN_QUOTE_SENDER, $storeScope))
                ->addTo($data['customeremail'])
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();

            //Save Data in Our Table : emailproductquote
            $pId = array();
            $pSKU = array();
            foreach ($quote->getAllItems() as $item) {
                $pId[] = $item->getProduct()->getId();
                $pSKU[] = $item->getProduct()->getSku();
            }
            $modelEmailProduct = $this->_EmailproductquoteFactory->create();
            $modelEmailProduct->setQuoteId($quote->getEntityId())
                ->setProductId(implode(",", $pId))
                ->setProductSku(implode(",", $pSKU))
                ->setCustomerEmail($customeremail)
                ->setCustomerName($customername)
                ->setTelephone($telephone)
                ->setComment($comment)
                ->setGrandTotal($grandtotal)
                ->setStatus(2)
                ->setStoreview($this->_modelStoreManagerInterface->getStore()->getId())
                ->save();
            return true;

        } catch (\Exception $e) {
            return false;
        }
    }
    public function deleteQuote($quoteid)
    {
        try {
            if($quoteid == '' || $quoteid == null){
                  return false;
            }
            $modelEmailProduct = $this->_EmailproductquoteFactory->create();
            $modelEmailProduct->load($quoteid);
            $modelEmailProduct->delete();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getSpecificQuote($quoteid)
    {
        try {
            if($quoteid == '' || $quoteid == null){
                  return null;
            }
            $modelEmailProduct = $this->_EmailproductquoteFactory->create();
            $modelEmailProduct->load($quoteid);
            return $modelEmailProduct->getData();
        } catch (\Exception $e) {
            return null;
        }
    }


    public function getAllQuote()
    {
        try {
            $modelEmailProduct = $this->_EmailproductquoteFactory->create()->getCollection();
            return $modelEmailProduct->getData();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getUpdateQuote($customername,$customeremail,$telephone,$comment,$emailquoteid)
    {
        try {

            $modelEmailProduct = $this->_EmailproductquoteFactory->create();
            $modelEmailProduct->load($emailquoteid);
            $data=$modelEmailProduct->getData();
            if(!isset($data['emailproductquote_id'])){
                return "not exist";
            }
                    $modelEmailProduct->setQuoteId($data['quote_id'])
                        ->setProductId($data['product_id']) 
                        ->setProductSku($data['product_sku'])
                        ->setCustomerEmail($customeremail)
                        ->setCustomerName($customername)
                        ->setTelephone($telephone)
                        ->setComment($comment)
                        ->setGrandTotal($data['grand_total'])      
                        ->save(); 
            return true;            
        } catch (\Exception $e) {
            return false;
        }
    }
    


}