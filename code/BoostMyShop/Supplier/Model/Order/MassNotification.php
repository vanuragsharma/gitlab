<?php

namespace BoostMyShop\Supplier\Model\Order;

class MassNotification extends Notification
{
    protected $_supplier;
    protected $date;
    protected $_poCollectionFactory;
    protected $_logger;

    public function __construct(
        \BoostMyShop\Supplier\Model\Config $config,
        \Magento\Framework\App\State $state,
        \BoostMyShop\Supplier\Model\Order\Notification\TransportBuilder $transportBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\CollectionFactory $poCollectionFactory,
        \BoostMyShop\Supplier\Helper\Logger $logger,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \BoostMyShop\Supplier\Model\Order\FtpUpload $ftpUpload
    )
    {
        $this->date = $date;
        $this->_logger = $logger;
        $this->_poCollectionFactory = $poCollectionFactory;

        parent::__construct($config, $state, $transportBuilder, $storeManager, $productMetadata, $ftpUpload);
    }

    public function processPendingNotifications()
    {
        //group expected POs by supplier
        $collection = $this->getPosToNotify();
        $this->_logger->log(count($collection)." pos to notify", "mass_notification");
        $data = [];
        $suppliers = [];
        foreach ($collection as $obj)
        {
            if(!isset($suppliers[$obj->getsup_id()]))
                $suppliers[$obj->getsup_id()] = $obj->getSupplier();
            $data[$obj->getsup_id()][$obj->getpo_id()] = $obj;
        }

        $results = [];

        foreach($data as $supplierId => $orders)
        {
            try
            {
                $this->_logger->log("Notify supplier #".$supplierId, "mass_notification");
                if ($this->notifySupplier($suppliers[$supplierId], $orders))
                {
                    $message = 'Notify to supplier #'.$suppliers[$supplierId]->getsup_name().' : ';
                    foreach($orders as $order)
                    {
                        $message .= $order->getpo_reference().', ';
                    }

                    $results[] = ['success' => 1, 'msg' => $message];
                }
            }
            catch(\Exception $ex)
            {
                //nothing, just to NOT break the loop
                $results[] = ['success' => 0, 'msg' => $ex->getMessage()];
            }
        }

        return $results;
    }

    protected function getPosToNotify()
    {
        $collection = $this->_poCollectionFactory->create();

        //todo : remove to_approve status (done as quick fix for matelpro)
        $collection->getSelect()->join($collection->getTable('bms_supplier'), 'po_sup_id = sup_id')
            ->where("
                    sup_delayed_notification = 1
                    AND po_delayed_notified != 1
                    AND po_status in ('".\BoostMyShop\Supplier\Model\Order\Status::toApprove."', '".\BoostMyShop\Supplier\Model\Order\Status::toConfirm."', '".\BoostMyShop\Supplier\Model\Order\Status::expected."')
            ");

        return $collection;

    }

    public function notifySupplier($supplier, $purchaseOrders)
    {
        $this->_supplier = $supplier;
        $supDelayedNotificationHours = explode(",", $this->_supplier->getsup_delayed_notification_hours());

        if(in_array((int)$this->date->gmtDate("H"), $supDelayedNotificationHours)) {
            $this->notifyToSupplier($purchaseOrders);
            return true;
        }
        return false;
    }
    public function notifyToSupplier($purchaseOrders)
    {
        $email = $this->_supplier->getsup_email();
        if (!$email)
            throw new \Exception('No email configured for this supplier');


        $storeId = $this->_supplier->getsup_website_id()? $this->_config->getStoreIdFromWebsiteId($this->_supplier->getsup_website_id()): 1;
        $params = $this->buildParams($purchaseOrders, $storeId);

        $name = $this->_supplier->getsup_contact();
        $template = $this->_config->getSetting('order/delay_email_template', $storeId);
        $sender = $this->_config->getSetting('order/email_identity', $storeId);

        $this->_sendEmailTemplate($template, $sender, $params, $storeId, $email, $name);
        foreach ($purchaseOrders as $purchaseOrder) {
            $purchaseOrder->addHistory(__('Supplier notified'));
            $purchaseOrder->setpo_delayed_notified("1")->save();
        }
    }

    protected function buildParams($purchaseOrders, $storeId = 1)
    {
        $datas = [];

        $allPoIds = array_keys($purchaseOrders);

        foreach($this->_supplier->getData() as $k => $v)
            $datas[$k] = $v;

        $datas['company_name'] = $this->_config->getGlobalSetting('general/store_information/name', $storeId);
        $datas['show_pdf_link'] = ($this->_supplier->getsup_attach_pdf() == "1" ? true : false);
        $datas['show_file_link'] = ($this->_supplier->getsup_attach_file() == "1" ? true : false);
        $datas['orders'] = $purchaseOrders;
        $po = reset($purchaseOrders);
        $datas['manager_fullname'] = $po->getManager()->getName();
        $datas['pdf_url'] = $this->getDownloadPdfUrl($allPoIds, $storeId);
        $datas['file_url'] = $this->getDownloadFileUrl($allPoIds, $storeId);

        return $datas;
    }

    protected function getDownloadPdfUrl($allPoIds, $storeId = 1)
    {
        $url = $this->_storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB).'supplier/po/massDownload/po_ids/'.urlencode(implode(",", $allPoIds)).'/type/pdf/token/'.$this->getToken($allPoIds);
        return $url;
    }

    protected function getDownloadFileUrl($allPoIds, $storeId = 1)
    {
        $url = $this->_storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB).'supplier/po/massDownload/po_ids/'.urlencode(implode(",", $allPoIds)).'/type/file/token/'.$this->getToken($allPoIds);
        return $url;
    }

    protected function getToken($allPoIds)
    {
        return md5(implode("", $allPoIds)."sup".$this->_supplier->getsup_id());
    }
}
