<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportHoldedCsv extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{
    public function execute()
    {

        $fileName = 'orders_holded.csv';
        $content = $this->_view->getLayout()->createBlock(
            'BoostMyShop\OrderPreparation\Block\Preparation\Tab\Holded'
        )->getCsv();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}