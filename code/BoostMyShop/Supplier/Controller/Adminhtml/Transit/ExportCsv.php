<?php
namespace BoostMyShop\Supplier\Controller\Adminhtml\Transit;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \BoostMyShop\Supplier\Controller\Adminhtml\Transit
{

    public function execute()
    {
        $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

        $this->_view->loadLayout();
        $content = $this->_view->getLayout()->createBlock('\BoostMyShop\Supplier\Block\Transit\Grid')->getCsv();
        $date = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
        $fileName = 'Product_transit_'. $date . '.csv';
        
        return $this->_fileFactory->create(
            $fileName,
            $content,
            DirectoryList::VAR_DIR
        );
    }
}