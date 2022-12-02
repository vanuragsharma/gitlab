<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\CarrierTemplate;

use Magento\Framework\App\Filesystem\DirectoryList;

class Manifest extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\CarrierTemplate
{
    public function execute()
    {
        $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

        $ctId = (int)$this->getRequest()->getParam('id');
        $carrierTemplate = $this->_carrierTemplateFactory->create()->load($ctId);

        try {
            $documentMimeType = 'application/pdf';
            $documentFileName = $carrierTemplate->getct_name().'_'.date('Ymd').'.pdf';

            $obj = $this->_objectManager->create('BoostMyShop\OrderPreparation\Model\Pdf\CarrierTemplateManifest');
            $documentContent = $obj->setCarrierTemplate($carrierTemplate)->getPdf()->render();

            $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
                $documentFileName,
                $documentContent,
                DirectoryList::VAR_DIR,
                $documentMimeType
            );

            //delete file
            $dir = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $dir->delete($documentFileName);

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/index');
        }
    }


}
