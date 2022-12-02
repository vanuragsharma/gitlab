<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\MassStockEditor;

class ProcessImport extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\MassStockEditor
{
    /**
     * @return void
     */
    public function execute()
    {
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

            $importHandler = $this->_objectManager->create('BoostMyShop\AdvancedStock\Model\Product\ImportHandler');
            $result = $importHandler->importFromCsvFile($fullPath, $delimiter);
            $unknownSku = implode(', ', $result['unknown'])?:0;
            $this->messageManager->addSuccess(__('Csv file has been imported : %1 rows processed, Errors: %2 ,unknown sku\'s : %3 ', $result['success'],$result['error'],$unknownSku ));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
        }

        $this->_redirect('*/*/index');

    }
}
