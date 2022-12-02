<?php

namespace BoostMyShop\Erp\Controller\Adminhtml\Products;

class MassNotDiscontinued extends \BoostMyShop\Erp\Controller\Adminhtml\Products
{

    public function execute()
    {
        $productIds = $this->getRequest()->getPost('massaction');
        if (!is_array($productIds))
            $productIds = explode(',', $productIds);

        $this->_productAction->updateAttributes($productIds, ['supply_discontinued' => 0], 0);

        $this->messageManager->addSuccess(__('Discontinued status updated'));
        $this->_redirect('erp/products/index');

    }

}