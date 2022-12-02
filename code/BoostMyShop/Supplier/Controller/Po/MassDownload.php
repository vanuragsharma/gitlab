<?php

namespace BoostMyShop\Supplier\Controller\Po;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class MassDownload extends \Magento\Framework\App\Action\Action
{

    protected $registry;
    protected $resultPageFactory;
    protected $httpContext;
    protected $_customerSession;
    protected $_adminNotification;
    protected $_orderFactory;
    protected $_fileExport;


    public function __construct(
        Context $context,
        Registry $registry,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \BoostMyShop\Supplier\Model\OrderFactory $orderFactory,
        \BoostMyShop\Supplier\Model\Order\FileExport $fileExport
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);

        $this->registry = $registry;
        $this->_orderFactory = $orderFactory;
        $this->httpContext = $httpContext;
        $this->_fileExport = $fileExport;

    }

    public function execute()
    {
        $poIds = explode(',',$this->getRequest()->getParam('po_ids'));
        $type = $this->getRequest()->getParam('type');
        $token = $this->getRequest()->getParam('token');

        $pos = $this->_orderFactory->create()->getCollection()->addFieldToFilter("po_id", ["in" => $poIds]);
        $supplier = $pos->getFirstItem()->getSupplier();

        if (($token) && ($token == $this->getToken($poIds, $supplier))) {
            switch ($type) {
                case 'file':
                    $fileData = '';
                    $fileData = $this->_fileExport->getFileContent($pos);
                    $fileName = $this->_fileExport->getFileName($pos, $supplier);
                    return $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
                        $fileName,
                        $fileData,
                        DirectoryList::VAR_DIR,
                        'text/plain'
                    );
                    break;
                default:
                    //return PO PDF
                    $pdf = $this->_objectManager->create('BoostMyShop\Supplier\Model\Pdf\Order')->getPdf($pos);
                    return $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
                        'purchase_orders.pdf',
                        $pdf->render(),
                        DirectoryList::VAR_DIR,
                        'application/pdf'
                    );
                    break;
            }
        }
    }

    protected function getToken($allPoIds, $sup)
    {
        return md5(implode("", $allPoIds)."sup".$sup->getsup_id());
    }
}
