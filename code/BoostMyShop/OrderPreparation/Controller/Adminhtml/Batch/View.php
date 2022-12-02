<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Batch;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;


class View extends \Magento\Backend\App\Action
{
    protected $logger;
    protected $_store;
    protected $_registry;
    protected $resultJsonFactory;
    protected $_batchFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        \Magento\Store\Api\Data\StoreInterface $store,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\OrderPreparation\Model\BatchFactory $batchFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {

        $this->logger            = $logger;
        $this->_store = $store;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_registry = $registry;
        $this->_batchFactory = $batchFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $bobId = $this->getRequest()->getParam("bob_id");
        $resultJson = $this->resultJsonFactory->create();

        if (!$this->_formKeyValidator->validate($this->getRequest()) || !$bobId)
        {
            $resultJson->setStatusHeader(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
            $response = [
                'message' => __('Batch Id or form key Invalid')
            ];
            return $resultJson->setData($response);
        }

        try {

            $batch = $this->_batchFactory->create()->load($bobId);
            $this->_registry->register('current_batch', $batch);

            $layoutFactory = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
            $html = $layoutFactory->getLayout()->getOutput();

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
            $this->logger->critical($exception);
        }

        return $resultJson->setData($response);
    }

    protected function _isAllowed()
    {
        return true;
    }
}
