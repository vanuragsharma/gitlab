<?php

namespace Catchers\Custom\Model\Order\Email;



class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    public function send()
    {//die('==sdddddd==');
        $this->configureEmailTemplate();

        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $object_manager->get('\Magento\Store\Model\StoreManagerInterface');
        $website_id = $storeManager->getWebsite()->getWebsiteId();
        $customer_factory = $object_manager->get('\Magento\Customer\Model\CustomerFactory');
        $customer_data = $customer_factory->create();
        $customer_data->setWebsiteId($website_id);
        $customer = $customer_data->loadByEmail($this->identityContainer->getCustomerEmail());
        $website_id = $storeManager->getWebsite()->getWebsiteId();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($customer->getData('cc_email_new'));
        $logger->info($customer->getData('cc_email_shipment'));
        $logger->info($customer->getData('cc_email_invoice'));

        $logger->info($this->identityContainer->getCustomerEmail());
        $logger->info($this->identityContainer->getCustomerName());

        if($this->identityContainer  instanceof \Magento\Sales\Model\Order\Email\Container\OrderIdentity) {
            if($customer->getData('cc_email_new') != ''){
                $addToOrderEmails = explode(",", $customer->getData('cc_email_new'));
                $this->transportBuilder->addTo(
                    $addToOrderEmails, 
                    $this->identityContainer->getCustomerName()
                );
                $this->transportBuilder->addCc($this->identityContainer->getCustomerEmail());
            }else{
                $this->transportBuilder->addTo(
                    $this->identityContainer->getCustomerEmail(),
                    $this->identityContainer->getCustomerName()
                );
            }
        } else if($this->identityContainer  instanceof \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity) {
            if($customer->getData('cc_email_invoice') != ''){
                $addToInvoiceEmails = explode(",", $customer->getData('cc_email_invoice'));
                $this->transportBuilder->addTo(
                    $addToInvoiceEmails, 
                    $this->identityContainer->getCustomerName()
                );
                $this->transportBuilder->addCc($this->identityContainer->getCustomerEmail());
            }else{
                $this->transportBuilder->addTo(
                    $this->identityContainer->getCustomerEmail(),
                    $this->identityContainer->getCustomerName()
                );
            }
        } else if($this->identityContainer instanceof \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity) {
            if($customer->getData('cc_email_shipment') != ''){
                $addToShipmentEmails = explode(",", $customer->getData('cc_email_shipment'));
                $this->transportBuilder->addTo(
                    $addToShipmentEmails, 
                    $this->identityContainer->getCustomerName()
                );
                $this->transportBuilder->addCc($this->identityContainer->getCustomerEmail());
            }else{
                $this->transportBuilder->addTo(
                    $this->identityContainer->getCustomerEmail(),
                    $this->identityContainer->getCustomerName()
                );
            }
        } else {
            $this->transportBuilder->addTo(
                $this->identityContainer->getCustomerEmail(),
                $this->identityContainer->getCustomerName()
            );
        }

        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
            foreach ($copyTo as $email) {
                $this->transportBuilder->addBcc($email);
            }
        }

        // Process for email to assigned group start
        $groupemail = [];
        $customer_group_data = $object_manager->get('\Catchers\Custom\Helper\Data')->customerGroupsConfig();
        if (!empty($customer_group_data)) {
            $groupemail = explode(",", $customer_group_data[$customer->getGroupId()]);
            if (!empty($groupemail)) {
                $this->transportBuilder->addBcc($groupemail);   
                $logger->info(print_r($groupemail, true)); 
            }                
        }      
        // Process for email to assigned group end
        
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }
}
