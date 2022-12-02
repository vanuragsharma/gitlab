<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class ProcessImportReception extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    public function execute()
    {
        $poId = $this->getRequest()->getParam('po_id');

        try
        {
            $destinationFolder = $this->_dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
            $uploader = $this->_uploaderFactory->create(array('fileId' => 'import_file'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setAllowedExtensions(['csv', 'txt']);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($destinationFolder);
            $fullPath = $result['path'].$result['file'];

            $delimiter = $this->getRequest()->getParam('delimiter');

            $importHandler = $this->_objectManager->create('BoostMyShop\Supplier\Model\Order\Reception\ImportHandler');
            $results = $importHandler->importFromCsvFile($poId, $fullPath, $delimiter);
            foreach($results['errors'] as $message)
            {
                $this->messageManager->addError(__($message));
            }
            if($results['success'] > 0)
                $this->messageManager->addSuccess(__('Csv file has been imported : %1 rows processed', $results['success']));

            $this->_redirect('*/*/edit', ['po_id' => $poId]);
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
            $this->_redirect('*/*/importReception', ['po_id' => $poId]);
        }

    }
}

