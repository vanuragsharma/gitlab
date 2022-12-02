<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Replenishment;

use \Magento\Backend\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Controller\ResultFactory;

class Popup extends Action
{
    /**
     * @var LoggerInterface $logger
     */
    protected $logger;
    protected $_timezone;
    protected $dateTimeFormatter;
    protected $_store;
    protected $_registry;



    /**
     * @var JsonFactory $resultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Content constructor.
     *
     * @param Context $context
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        DateTimeFormatterInterface $dateTimeFormatter,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Store\Api\Data\StoreInterface $store,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {

        $this->logger = $logger;
        $this->_timezone = $timezone;
        $this->_store = $store;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_registry = $registry;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     *
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $orderId = $this->getRequest()->getParam("order_id");
        $resultJson = $this->resultJsonFactory->create();

        if (!$this->_formKeyValidator->validate($this->getRequest()))
        {
            $resultJson->setStatusHeader(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
            $response = [
                'message' => __('form key Invalid')
            ];
            return $resultJson->setData($response);
        }

        try {
            $layoutFactory = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
            $html = $layoutFactory->getLayout()->getOutput();

            $response = [
                'content' => $html,
                'title' => __("Supply needs summary per supplier")
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
