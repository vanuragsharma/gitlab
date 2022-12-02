<?php

namespace Magecomp\Emailquotepro\Block;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;

class Emailquote extends Template
{
    protected $customersession;
    protected $quoteSession;
    protected $context;

    public function __construct(
        CustomerSession $customersession,
        Session $quoteSession,
        Context $context,
        array $data = [] )
    {
        parent::__construct($context, $data);
        $this->customersession = $customersession;
        $this->quoteSession=$quoteSession;
    }

    public function getHomeUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function getCartUrl()
    {
        return $this->getUrl('*/*/*',['_secure' => true]);
    }
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('emailquotepro/index/sendmail',['_secure' => true]);
    }

    public function getCustomerIdData()
    {
        if ($this->customersession->isLoggedIn()) {
            $customer = $this->customersession->getCustomer();
            return $customer->getEntityId();
        } else {
            return 0;
        }
    }
    public function getSessionData()
    {
        return $this->quoteSession->getQuote();
    }
    public function getCustomerNameData()
    {
        if ($this->customersession->isLoggedIn()) {
            $customer = $this->customersession->getCustomer();
            return $customer->getFirstname() . ' ' . $customer->getLastname();
        } else {
            return '';
        }
    }

    public function getCustomerEmailData()
    {
        if ($this->customersession->isLoggedIn()) {
            $customer = $this->customersession->getCustomer();

            return $customer->getEmail();
        } else {
            return '';
        }
    }

    public function getCustomerTelephoneData()
    {
        if ($this->customersession->isLoggedIn()) {
            $customer = $this->customersession->getCustomer();
            $address = $customer->getPrimaryBillingAddress();
            if ($address) {
                return $address->getTelephone();
            } else {
                return '';
            }
        } else {
            return '';
        }
    }
}