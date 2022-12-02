<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportInProgressCsv extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{
    public function execute()
    {

        $fileName = 'orders_inprogress.csv';
        $content = $this->_view->getLayout()->createBlock(
            'BoostMyShop\OrderPreparation\Block\Preparation\InProgress'
        )->getCsv();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}