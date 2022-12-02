<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\MassStockEditor;

class Import extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\MassStockEditor
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Import Barcodes'));
        $this->_view->renderLayout();
    }
}
