<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier;

class Index extends \BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        $resultPage = $this->_resultPageFactory->create();
        $gridBlock = $resultPage->getLayout()->getBlock('productsupplier.grid.container');
        if (!$gridBlock->hasSupplierOrProductFilter())
        {
            $headerBlock = $resultPage->getLayout()->getBlock('productsupplier.header');
            $headerBlock->setShowFilterMessage(true);
        }

        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Product / Supplier association'));
        $this->_view->renderLayout();
    }
}
