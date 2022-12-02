<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class MassShippingLabelDownload extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{

    protected $resultForwardFactory;

    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        try
        {
            $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

            $inProgressIds = $this->getRequest()->getPost('massaction');
            if (!is_array($inProgressIds))
                $inProgressIds = explode(',', $inProgressIds);

            $templateId = $this->getRequest()->getPost('template_id');
            $template = $this->_carrierTemplateFactory->create()->load($templateId);

            $collection = $this->_inProgressCollectionFactory->create()->addFieldToFilter('ip_id', ['in' => $inProgressIds]);

            $documentContent = $template->getShippingLabelFile($collection, true);
            $documentMimeType = $template->getct_export_file_mime();
            $documentFileName = $template->getct_export_file_name();

            $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
                $documentFileName,
                $documentContent,
                DirectoryList::VAR_DIR,
                $documentMimeType
            );

            //delete file
            $dir = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $dir->delete($documentFileName);
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('An error occured : %1', $ex->getMessage()));
            $this->_redirect('*/*/index');
        }

    }
}
