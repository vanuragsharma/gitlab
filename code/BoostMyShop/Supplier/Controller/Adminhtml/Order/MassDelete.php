<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

class MassDelete extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    public function execute()
    {
        $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

        $ids = $this->getRequest()->getPost('entity_ids');
        if (!is_array($ids))
            $ids = explode(',', $ids);
        $count = 0;

        try{

            $collection = $this->_orderCollectionFactory->create()->addFieldToFilter('po_id', ['in' => $ids]);
            foreach($collection as $order){
                $order->delete();
                $count++;
            }

        } catch(\Exception $e){
            $this->messageManager->addError(__('An error occurred : '.$e->getMessage()));
        }

        $this->messageManager->addSuccess(__('%1 orders deleted', $count));
        $this->_redirect('*/*/index');
    }


}
