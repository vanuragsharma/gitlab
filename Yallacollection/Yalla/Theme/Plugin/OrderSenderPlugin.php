<?php

namespace Yalla\Theme\Plugin;

use Magento\Sales\Model\Order;

class OrderSenderPlugin {
    public function aroundSend( \Magento\Sales\Model\Order\Email\Sender\OrderSender $subject, callable $proceed, Order $order, $forceSyncMode = false ) {
        $payment = $order->getPayment()->getMethodInstance()->getCode();

        if ( $payment === 'qpaypayment' && $order->getStatus() !== 'paymentcomplete' ) {
            return false;
        }

        return $proceed( $order, $forceSyncMode );
    }
}
