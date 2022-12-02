<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Controller\ResultFactory;

class HoldOrderPopup extends \Magento\Backend\App\Action
{

    protected $resultJsonFactory;
    protected $_inProgressFactory;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\OrderPreparation\Model\InProgressFactory $inProgressFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_inProgressFactory = $inProgressFactory;
        $this->_registry = $registry;
        parent::__construct($context);
    }


    /**
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $orderId = $this->getRequest()->getParam("order_id");
        $resultJson = $this->resultJsonFactory->create();
        $orderInProgress = $this->_inProgressFactory->create()->load($orderId);
        $this->_registry->register('current_packing_order', $orderInProgress);
        $order = $orderInProgress->getOrder();
        if (!$this->_formKeyValidator->validate($this->getRequest()) || !$orderId)
        {
            $resultJson->setStatusHeader(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
            $response = [
                'message' => __('Order Id or form key Invalid')
            ];
            return $resultJson->setData($response);
        }
        $this->_view->renderLayout();

        try {

            $incrementId = $this->cleanReference($order->getincrement_id());
            $layoutFactory = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
            $html = $layoutFactory->getLayout()->getOutput();

            $response = [
                'content' => $html,
                'title' => __("Order #".$incrementId)
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

    public function cleanReference($reference)
    {
        $t = explode('_', $reference);
        if (isset($t[0]) && count($t) > 1)
            unset($t[0]);
        return implode('_', $t);
    }

    protected function _isAllowed()
    {
        return true;
    }
}
