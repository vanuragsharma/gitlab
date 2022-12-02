<?php
namespace BoostMyShop\Supplier\Controller\Adminhtml\Supplier;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \BoostMyShop\Supplier\Controller\Adminhtml\Supplier
{

    public function execute()
    {
        $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

        $this->_view->loadLayout();
        $fileName = 'suppliers.csv';
        $content = $this->_view->getLayout()->getChildBlock('supplier.grid', 'grid.export');

        return $this->_fileFactory->create(
            $fileName,
            $content->getCsvFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}