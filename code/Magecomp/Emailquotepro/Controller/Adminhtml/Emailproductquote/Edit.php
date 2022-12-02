<?php

namespace Magecomp\Emailquotepro\Controller\Adminhtml\Emailproductquote;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Customer;
use Magento\Backend\Model\Session\Quote;
use Magecomp\Emailquotepro\Model\EmailproductquoteFactory;

class Edit extends Action
{
    protected $_coreRegistry = null;
    protected $resultPageFactory;
    protected $quoteFactory;
    protected $customerModel;
    protected $backendQuote;
    protected $_EmailproductquoteFactory;

    public function __construct( Action\Context $context,
                                 PageFactory $resultPageFactory,
                                 Registry $registry,
                                 QuoteFactory $quoteFactory,
                                 Quote $backendQuote,
                                 Customer $customermodel,
                                 StoreManagerInterface $storeManager,
                                 EmailproductquoteFactory $EmailproductquoteFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->quoteFactory = $quoteFactory;
        $this->customerModel = $customermodel;
        $this->backendQuote = $backendQuote;
        $this->_storeManager = $storeManager;
        $this->_EmailproductquoteFactory = $EmailproductquoteFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        $quoteid = $this->getRequest()->getParam('quote_id');
        $customerEmail = $this->getRequest()->getParam('customer_email');
        $customerName = $this->getRequest()->getParam('customer_name');
        $customerData = $this->customerModel->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())->loadByEmail($customerEmail);
        $customerId = 0;

        $modelEmailProduct = $this->_EmailproductquoteFactory->create()->load($quoteid, 'quote_id');
        $quoteStoreId= $modelEmailProduct->getStoreview();

        if (!($customerData->getId())) {
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $store = $this->_storeManager->getStore();

            $customer = $this->customerModel;
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($customerName)
                ->setLastname('User')
                ->setEmail($customerEmail)
                ->setPassword('123456789');
            try {
                $customer->save();
                $storeId = $customer->getSendemailStoreId();
                $customer->sendNewAccountEmail('registered', '', $storeId);
                $quote = $this->quoteFactory->create()->load($quoteid);
                $quote->setCustomerId((int)$customerId);
                $quote->setCustomerEmail($customerEmail);
                $quote->setCustomerFirstname($customerName);
                $quote->save();
                $customerId = (int)$customer->getId();

            } catch (\Exception $e) {
            }
        } else {
            $customerId = (int)$customerData->getId();
        }

        $this->backendQuote->setCustomerId($customerId);
        $this->backendQuote->setQuoteId($quoteid);
        $this->backendQuote->setStoreId($quoteStoreId);
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/order_create');
    }

    protected function _isAllowed()
    {
        return true;
    }
}
