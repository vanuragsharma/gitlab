<?php

namespace Catchers\Custom\Model\Order\Email;



class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    public function send()
    {//die('==sdddddd==');
        $this->configureEmailTemplate();

        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $object_manager->get('\Magento\Store\Model\StoreManagerInterface');
        // print_r($storeManager); die;

        $website_id = $storeManager->getWebsite()->getWebsiteId();
        $customer_factory = $object_manager->get('\Magento\Customer\Model\CustomerFactory');
        $customer_data = $customer_factory->create();
        $customer_data->setWebsiteId($website_id);
        $customer = $customer_data->loadByEmail($this->identityContainer->getCustomerEmail());
        $website_id = $storeManager->getWebsite()->getWebsiteId();

        echo $userGroupId = $customer->getData('group_id');
        // $sellerFactory = $object_manager->get('\Magento\Customer\Model\CustomerFactory');
        // $sellerData = $sellerFactory->create();
        // $sellerData->setWebsiteId($website_id);
        // $sellerInfo = $sellerData->load($userGroupId);

        $sellerObject = \Magento\Framework\App\ObjectManager::getInstance();
        $sellerInfo = $sellerObject->create('Magento\Customer\Model\Customer')
                    ->load($userGroupId);

        echo $sellerInfo->getEmail();
        echo $sellerInfo->getFirstName();
        echo $sellerInfo->getLastName(); die;
        

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($customer->getData('cc_email_new'));
        $logger->info($customer->getData('cc_email_shipment'));
        $logger->info($customer->getData('cc_email_invoice'));

        if($this->identityContainer  instanceof \Magento\Sales\Model\Order\Email\Container\OrderIdentity) {
            if($customer->getData('cc_email_new') != ''){
                $this->transportBuilder->addTo(
                    $customer->getData('cc_email_new'), 
                    $this->identityContainer->getCustomerName()
                );
                $this->transportBuilder->addCc($this->identityContainer->getCustomerEmail());
            }else{
                $this->transportBuilder->addTo(
                    $this->identityContainer->getCustomerEmail(),
                    $this->identityContainer->getCustomerName()
                );
            }
        }

        if($this->identityContainer  instanceof \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity) {
            if($customer->getData('cc_email_invoice') != ''){
                $this->transportBuilder->addTo(
                    $customer->getData('cc_email_invoice'), 
                    $this->identityContainer->getCustomerName()
                );
                $this->transportBuilder->addCc($this->identityContainer->getCustomerEmail());
            }else{
                $this->transportBuilder->addTo(
                    $this->identityContainer->getCustomerEmail(),
                    $this->identityContainer->getCustomerName()
                );
            }
        }

        if($this->identityContainer instanceof \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity) {
            if($customer->getData('cc_email_shipment') != ''){
                $this->transportBuilder->addTo(
                    $customer->getData('cc_email_shipment'), 
                    $this->identityContainer->getCustomerName()
                );
                $this->transportBuilder->addCc($this->identityContainer->getCustomerEmail());
            }else{
                $this->transportBuilder->addTo(
                    $this->identityContainer->getCustomerEmail(),
                    $this->identityContainer->getCustomerName()
                );
            }
        }

        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
            foreach ($copyTo as $email) {
                $this->transportBuilder->addBcc($email);
            }
        }
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }
}
