<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest;

use Magento\Framework\Controller\ResultFactory;

class Export extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest
{
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        try {
            $carrierId = $this->getRequest()->getParam("carrierId");
            $manifestId = $this->getRequest()->getParam("manifestId");
            $manifest = $this->_manifestFactory->create()->load($manifestId);
            $carrierTemplate = $this->_carrierTemplateFactory->create()->load($carrierId);
            $carrierTemplate->getRenderer()->sendManifestEdi($carrierTemplate, $manifest);
            $manifest->setbom_edi_status(\BoostMyShop\OrderPreparation\Model\Manifest::STATUS_SENT)->save();
            $response = ['text' => __('Send again EDI')];

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