<?php

namespace MyFatoorah\MyFatoorahPaymentGateway\Controller\Checkout;

/**
 * @package MyFatoorah\MyFatoorahPaymentGateway\Controller\Checkout
 */
class Cancel extends AbstractAction {

    public function execute() {

        $error = $this->getRequest()->get('error');
        $this->getMessageManager()->addErrorMessage(__($error));

        $orderId = $this->getRequest()->get('orderId');
        $order   = $this->getOrderById($orderId);
        if ($order && $order->getId()) {

            $this->log->info("Cancel Order ----- Order# " . $orderId);
						
            $this->getCheckoutHelper()->cancelCurrentOrder('Invoice Creation Error - ' . $error);
			
			$orderStatus = $this->getGatewayConfig()->getMyFatoorahApprovedOrderStatus();
			$order->setState(Order::STATE_PENDING_PAYMENT)
                ->setStatus($orderStatus)->save();

        }

        $this->getCheckoutHelper()->restoreQuote(); //restore cart
        $this->_redirect('checkout/cart');
    }

}
