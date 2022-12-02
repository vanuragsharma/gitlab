<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest;

use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest
{

    public function execute()
    {
        $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

        $manifestId = (int)$this->getRequest()->getParam('id');
        try {
            $documentMimeType = 'application/pdf';
            $documentFileName = 'manifest_'.date('Ymd').'.pdf';

            $obj = $this->_objectManager->create('BoostMyShop\OrderPreparation\Model\Pdf\CarrierTemplateManifest');
            $documentContent = $obj->setManifestId($manifestId)->getPdf()->render();

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
