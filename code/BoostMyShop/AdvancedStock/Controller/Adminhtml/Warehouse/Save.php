<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse;

class Save extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Warehouse
{
    public function execute()
    {
        $stockId = (int)$this->getRequest()->getParam('w_id');
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            $this->_redirect('adminhtml/*/');
            return;
        }

        $model = $this->_warehouseFactory->create()->load($stockId);
        if ($stockId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This warehouse no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }

        if($data['w_sync_stock_from_po'])
            $data['w_disable_stock_movement'] = 1;

        $model->setData($data);


        try {
            $model->save();

            //if stock movement are disabled, delete existing stock movements
            if ($model->getOrigData('w_disable_stock_movement') == 0 && $model->getw_disable_stock_movement() ==1)
            {
                $this->deleteStockMovements($model->getId());
            }

            $importResult = $this->checkImport($stockId);
            if ($importResult)
            {
                $this->_initAction();
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Import results'));

                $block = $this->_view->getLayout()->getBlock('warehouse.import.result');
                $block->setWarehouse($model);
                $block->setImportResult($importResult);

                $this->_view->renderLayout();
            }
            else
            {
                $this->messageManager->addSuccess(__('You saved the warehouse.'));
                $this->_redirect('*/*/Edit', ['w_id' => $model->getId()]);
            }

        } catch (\Magento\Framework\Validator\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->redirectToEdit($model, $data);
        }
    }

    /**
     * @param
     * @param array $data
     * @return void
     */
    protected function redirectToEdit(\BoostMyShop\Supplier\Model\Supplier $model, array $data)
    {
        $this->_getSession()->setUserData($data);
        $arguments = $model->getId() ? ['w_id' => $model->getId()] : [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        $this->_redirect('adminhtml/*/edit', $arguments);
    }

    protected function checkImport($stockId)
    {
        try
        {
            $adapter = $this->_httpFactory->create();
            if ($adapter->isValid('import_file')) {
                $destinationFolder = $this->_dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
                $uploader = $this->_uploaderFactory->create(array('fileId' => 'import_file'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setAllowedExtensions(['csv', 'txt']);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                $fullPath = $result['path'].$result['file'];

                $postValue = $this->getRequest()->getPostValue();
                $separator = isset($postValue['separator']) ? $postValue['separator'] : ';';

                $importHandler = $this->_objectManager->create('BoostMyShop\AdvancedStock\Model\Warehouse\ProductsImportHandler');
                $count = $importHandler->importFromCsvFile($stockId, $fullPath, $separator);

                return $importHandler->getResult();
            }

        }
        catch(\Exception $ex)
        {
            //nothing
            $this->messageManager->addError(__('An error occured during import : %1', $ex->getMessage()));
        }

        return false;
    }

    protected function deleteStockMovements($warehouseId)
    {
        //todo : to implement
    }

}
