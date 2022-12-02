<?php

namespace BoostMyShop\OrderPreparation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class OrderCancelAfter implements ObserverInterface
{
    protected $_context;
    protected $_inProgressFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \BoostMyShop\OrderPreparation\Model\InProgressFactory $inProgressFactory
    ) {
        $this->_context = $context;
        $this->_inProgressFactory = $inProgressFactory;
    }

    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $inProgress = $this->_inProgressFactory->create()->loadByOrderReference($order->getincrement_id());
        if($inProgress->getId())
        {
            $errorMsg = __('Order #%1 can not be canceled as it is part of in progress orders', $order->getincrement_id());
            $this->_context->getMessageManager()->addError($errorMsg);
            throw new \Exception($errorMsg);
        }

        return $this;
    }

}
