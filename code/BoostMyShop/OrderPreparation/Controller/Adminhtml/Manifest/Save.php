<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest;

use Magento\Framework\Controller\ResultFactory;

class Save extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest
{
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key.'));
            $this->_redirect('*/*/index/');
        }
        $resultJson = $this->resultJsonFactory->create();

        try {
            $data = $this->getRequest()->getPostValue();

            if(!$data['carrier'] || !$data['warehouse_id'] || !$data['shipment_ids'])
            {
                $this->messageManager->addErrorMessage(__('parameters missing.'));
                $this->_redirect('*/*/create');
            }else{
                $this->_manifestHelper->createManifest($data['carrier'], $data['warehouse_id'], $data['shipment_ids']);
                $this->messageManager->addSuccess(__('Manifest Created.'));
                $this->_redirect('*/*/index/');
            }

        } catch (\Exception $exception) {
            $this->messageManager->addError($exception->getMessage());
            $this->_redirect('*/*/index/');
        }
    }
}