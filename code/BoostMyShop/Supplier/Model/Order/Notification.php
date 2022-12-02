<?php

namespace BoostMyShop\Supplier\Model\Order;

class Notification
{
    protected $_config;
    protected $_transportBuilder;
    protected $_storeManager;
    protected $_state;
    protected $_productMetadata;
    protected $_ftpUpload;

    public function __construct(
        \BoostMyShop\Supplier\Model\Config $config,
        \Magento\Framework\App\State $state,
        \BoostMyShop\Supplier\Model\Order\Notification\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \BoostMyShop\Supplier\Model\Order\FtpUpload $ftpUpload
    )
    {
        $this->_config = $config;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_state = $state;
        $this->_productMetadata = $productMetadata;
        $this->_ftpUpload = $ftpUpload;
    }

    public function notifyToSupplier($purchaseOrder)
    {
        $supplier = $purchaseOrder->getSupplier();

        if($supplier->getsup_delayed_notification())
            return true;

        if($supplier->getsup_notif_ftp_enabled())
            $this->_ftpUpload->process($purchaseOrder);

        $email = $supplier->getsup_email();
        $name = $supplier->getsup_contact();
        $storeId = ($purchaseOrder->getpo_store_id() ? $purchaseOrder->getpo_store_id() : 1);
        if (!$email)
            throw new \Exception('No email configured for this supplier');

        $template = $this->_config->getSetting('order/email_template', $storeId);
        $sender = $this->_config->getSetting('order/email_identity', $storeId);

        $params = $this->buildParams($purchaseOrder);

        $this->_sendEmailTemplate($template, $sender, $params, $storeId, $email, $name);

        if($supplier->getsup_po_notified_status()) {
            $purchaseOrder->setpo_status($supplier->getsup_po_notified_status())->save();
            $purchaseOrder->updateQtyToReceive();
        }

        // adding history
        $purchaseOrder->addHistory(__('Supplier notified').' : '.$email);

    }

    protected function _sendEmailTemplate($template, $sender, $templateParams = [], $storeId, $recipientEmail, $recipientName)
    {
        $copyTo = $this->getEmailCopyTo($storeId);

        $recipientEmails = explode(',', $recipientEmail);

        $transport = $this->_transportBuilder->setTemplateIdentifier(
                            $template
                        )->setTemplateOptions(
                            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                        )->setTemplateVars(
                            $templateParams
                        );

        if(version_compare($this->getMagentoVersion(), "2.3.3", ">=")){
            $transport->setFromByScope(
                $sender, $storeId
            );
        } else {
            $transport->setFromByStoreId(
                $sender, $storeId
            );
        }

        foreach($recipientEmails as $recipientEmail)
        {
            $recipientEmail = trim($recipientEmail);
            $transport->addTo(
                                $recipientEmail,
                                $recipientName
                            );
        }

        if (!empty($copyTo)) {
            foreach ($copyTo as $email) {
                $this->_transportBuilder->addCc($email);
            }
        }

        $transport= $this->_transportBuilder->getTransport();

        $transport->sendMessage();

        return $this;
    }

    protected function buildParams($purchaseOrder)
    {
        $datas = [];

        foreach($purchaseOrder->getData() as $k => $v)
            $datas[$k] = $v;

        foreach($purchaseOrder->getSupplier()->getData() as $k => $v)
            $datas[$k] = $v;

        $datas['manager_fullname'] = $purchaseOrder->getManager()->getName();
        $datas['delivery_address'] = str_replace("\n", "<br/>", $purchaseOrder->getBillingAddress());
        $datas['shipping_address'] = str_replace("\n", "<br/>", $purchaseOrder->getShippingAddress());
        $datas['company_name'] = $this->_config->getGlobalSetting('general/store_information/name', $purchaseOrder->getpo_store_id());

        $datas['order'] = $purchaseOrder;
        $datas['supplier'] = $purchaseOrder->getSupplier();

        $datas['show_pdf_link'] = ($purchaseOrder->getSupplier()->getsup_attach_pdf() == "1" ? true : false);
        $datas['pdf_url'] = $this->getDownloadPdfUrl($purchaseOrder);
        $datas['show_file_link'] = ($purchaseOrder->getSupplier()->getsup_attach_file() == "1" ? true : false);
        $datas['file_url'] = $this->getDownloadFileUrl($purchaseOrder);
        $datas['show_shipping_label_link'] = ($purchaseOrder->getShippingLabelUrl() ? true : false);
        $datas['shipping_label_url'] = $purchaseOrder->getShippingLabelUrl();

        $datas['po_created_at'] = $purchaseOrder->formatDate($datas['po_created_at']);
        $datas['po_updated_at'] = $purchaseOrder->formatDate($datas['po_updated_at']);
        $datas['po_eta'] = $purchaseOrder->formatDate($datas['po_eta']);

        return $datas;
    }

    protected function getDownloadPdfUrl($purchaseOrder)
    {
        //getUrl from admi doesnt work, dirty workaround below (git issue : https://github.com/magento/magento2/issues/5322)
        $url = $this->_storeManager->getStore($purchaseOrder->getpo_store_id())->getUrl('supplier/po/download', ['_area' => 'frontend', 'po_id' => $purchaseOrder->getId(), 'type' => 'pdf',  'token' => $purchaseOrder->getToken(), '_nosid' => 1]);
        $url = $this->_storeManager->getStore($purchaseOrder->getpo_store_id())->getBaseUrl().'supplier/po/download/po_id/'.$purchaseOrder->getId().'/type/pdf/token/'.$purchaseOrder->getToken();
        return $url;
    }

    protected function getDownloadFileUrl($purchaseOrder)
    {
        //getUrl from admi doesnt work, dirty workaround below (git issue : https://github.com/magento/magento2/issues/5322)
        $url = $this->_storeManager->getStore($purchaseOrder->getpo_store_id())->getUrl('supplier/po/download', ['_area' => 'frontend', 'po_id' => $purchaseOrder->getId(), 'type' => 'file',  'token' => $purchaseOrder->getToken(), '_nosid' => 1]);
        $url = $this->_storeManager->getStore($purchaseOrder->getpo_store_id())->getBaseUrl().'supplier/po/download/po_id/'.$purchaseOrder->getId().'/type/file/token/'.$purchaseOrder->getToken();
        return $url;
    }

    /**
     * Return email copy_to list
     *
     * @return array|bool
     */
    protected function getEmailCopyTo($storeId)
    {
        $data = $this->_config->getSetting('order/copy_to', $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    public function getMagentoVersion()
    {
        return $this->_productMetadata->getVersion();
    }
}
