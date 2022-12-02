<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest;

use Magento\Framework\Controller\ResultFactory;

class View extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest
{
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $manifestId = $this->getRequest()->getParam("bom_id");
        $resultJson = $this->resultJsonFactory->create();

        if (!$this->_formKeyValidator->validate($this->getRequest()) || !$manifestId)
        {
            $resultJson->setStatusHeader(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
            $response = [
                'message' => __('Manifest Id or form key Invalid')
            ];
            return $resultJson->setData($response);
        }

        try {
            $manifest = $this->_manifestFactory->create()->load($manifestId);
            $this->_registry->register('current_manifest', $manifest);
            $resultPage = $this->resultPageFactory->create();
            $html = $resultPage->getLayout()->getOutput();

            $response = [
                'content' => $html
            ];
        } catch (\Exception $exception) {
            $resultJson->setStatusHeader(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
            /** @var array $response */
            $response = [
                'message' => __('An error occurred : '.$exception->getMessage()),
                'stack_trace' => $exception->getTraceAsString()
            ];
        }

        return $resultJson->setData($response);
    }
}