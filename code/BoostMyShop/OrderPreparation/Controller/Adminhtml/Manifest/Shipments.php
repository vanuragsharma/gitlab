<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest;

use Magento\Framework\Controller\ResultFactory;

class Shipments extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest
{
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        try {
            $layout = $this->_view->getLayout();
            $resultPage = $this->resultPageFactory->create();
            $html = $resultPage->getLayout()->getBlock('shipment.list')->toHtml();

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