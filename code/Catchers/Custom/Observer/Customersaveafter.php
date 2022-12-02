<?php

namespace Catchers\Custom\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class Customersaveafter implements ObserverInterface
{
    protected $_request;
    protected $_layout;
    protected $_objectManager = null;
    protected $_customerRepository;

    /**
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->_layout = $context->getLayout();
        $this->_request = $context->getRequest();
        $this->_objectManager = $objectManager;
        $this->_customerRepository = $customerRepository;
    }

    /**
    * @param \Magento\Framework\Event\Observer $observer
    * @return void
    */
    public function execute(EventObserver $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $requestParams = $observer->getEvent()->getRequest()->getParams();
        $customerParam = $requestParams['customer'];
        $customer = $this->_customerRepository->getById($customer->getId());

        if(array_key_exists('cc_email_new', $customerParam)){
            $ccNewEmail = $customerParam['cc_email_new'];

            $customer->setCustomAttribute('cc_email_new',$ccNewEmail);
        }

        if(array_key_exists('cc_email_shipment', $customerParam)){
            $ccShipmentEmail = $customerParam['cc_email_shipment'];

            $customer->setCustomAttribute('cc_email_shipment',$ccShipmentEmail);
        }

        if(array_key_exists('cc_email_invoice', $customerParam)){
            $ccInvoiceEmail = $customerParam['cc_email_invoice'];

            $customer->setCustomAttribute('cc_email_invoice',$ccInvoiceEmail);
        }

        $this->_customerRepository->save($customer);
        
    }
}