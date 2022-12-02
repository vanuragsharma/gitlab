<?php

namespace BoostMyShop\Erp\Controller\Adminhtml\Products;

class MassDelete extends \BoostMyShop\Erp\Controller\Adminhtml\Products
{

    public function execute()
    {
        try
        {
            $productIds = $this->getRequest()->getPost('massaction');
            if (!is_array($productIds))
                $productIds = explode(',', $productIds);

            foreach($productIds as $productId)
            {
                $product = $this->_productFactory->create()->load($productId);
                $product->delete();
            }

            $this->messageManager->addSuccess(__('%1 products deleted', count($productIds)));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addSuccess(__('An error occured : %1', $ex->getMessage()));
        }


        $this->_redirect('erp/products/index');

    }

}