<?php

namespace MyFatoorah\MyFatoorahPaymentGateway\Controller\Checkout;

use Magento\Sales\Model\Order;

/**
 * @package MyFatoorah\MyFatoorahPaymentGateway\Controller\Checkout
 */
class Success extends AbstractAction {

    private $cards = [
        'KNET'                                 => 'kn',
        'VISA/MASTER'                          => 'vm',
        'MADA'                                 => 'md',
        'Benefit'                              => 'b',
        'Qatar Debit Cards'                    => 'np',
        'UAE Debit Cards'                      => 'uaecc',
        'Sadad'                                => 's',
        'AMEX'                                 => 'ae',
        'Apple Pay'                            => 'ap',
        'KFast'                                => 'kf',
        'AFS'                                  => 'af',
        'STC Pay'                              => 'stc',
        'Mezza'                                => 'mz',
        'Orange Cash'                          => 'oc',
        'Oman Net'                             => 'on',
        'Mpgs'                                 => 'M',
        'UAE DEBIT VISA'                       => 'ccuae',
        'VISA/MASTER Saudi'                    => 'vms',
        'VISA/MASTER/MADA'                     => 'vmm',
        //same gateway with diff name
        'Visa/Master Direct 3DS Flow'          => 'vm', //test gateways
        'Visa/Master Direct (Token/Recurring)' => 'vm', //test gateways
        'Visa/Master Direct'                   => 'vm', //test gateways
        'VISA/MASTER Qatar'                    => 'vm',
        'BenefitV2'                            => 'b',
        'Apple Pay (Mada)'                     => 'ap',
        'Debit/Credit Cards'                   => 'uaecc',
        'VISA/Mastercard'                      => 'vm',
        'Debit Cards UAE'                      => 'uaecc',
    ];

    public function execute() {

        try {
            $paymentId = $this->getRequest()->get('paymentId');
            if (!$paymentId) {
                throw new \Exception('MyFatoorah returned a null payment id. This may indicate an issue with the myfatoorah payment gateway.');
            }

            $error = $this->checkStatus($paymentId, 'paymentId', $this->myfatoorah);
            if (!$error) {
                //redirect to success page
                $this->getMessageManager()->addSuccessMessage(__('Your payment is complete'));
                $this->_redirect('checkout/onepage/success', array('_secure' => false));
                return;
            }
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            $this->log->info('In Exception Block: ' . $error);
            //$this->log->info('In Exception Block: ' . $ex->getTraceAsString());
        }
        //restore cart
        $this->getCheckoutHelper()->restoreQuote();

        //redirect to cancel page
        $this->getMessageManager()->addErrorMessage($error);
        $this->_redirect('checkout/cart', array('_secure' => false));
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function checkStatus($keyId, $KeyType, $myfatoorah, $source = '', $escape = false) {

        //check for the invoice or payment id
        $data = $myfatoorah->getPaymentStatus($keyId, $KeyType);

        //get the order
        /** @var Order $order */
        $order = $this->getOrderById($data->CustomerReference);
        if (!$order) {
            throw new \Exception("MyFatoorah returned an id: $data->CustomerReference for an order that could not be retrieved");
        }

        //order is not pending or canceled
        if (!$escape && $order->getState() !== Order::STATE_PENDING_PAYMENT && $order->getState() !== Order::STATE_CANCELED) {
            return false;
        }


        $message = "MyFatoorah$source: $data->InvoiceStatus Payment. ";
        $message .= isset($data->focusTransaction) ? 'Payment Id #' . $data->focusTransaction->PaymentId . '. Gateway used is ' . $data->focusTransaction->PaymentGateway . '. ' : '';

        if ($data->InvoiceStatus == 'Paid') {
            $this->savePaymentData($data);
            $this->processPaidPayment($order, $data->InvoiceId, $message);
        } else if ($data->InvoiceStatus == 'Failed') {
            $this->savePaymentData($data);
            $this->processCancelPayment($order, $message . 'Gateway error is ' . $data->InvoiceError);
        } else if ($data->InvoiceStatus == 'Expired') {
            $this->processCancelPayment($order, $message . $data->InvoiceError);
        }

        return $data->InvoiceError;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param object $data
     * @return void
     */
    private function savePaymentData($data) {

        $orderId = $data->CustomerReference;
        //save the invoice id in myfatoorah_invoice table 
        //see this sol: https://stackoverflow.com/questions/12570752/how-do-i-select-a-single-row-in-magento-in-a-custom-database-for-display-in-a-bl
        //$collection = Mage::getModel('brands/brands')->getCollection();

        $collection = $this->mfInvoiceFactory->create()->addFieldToFilter('invoice_id', $data->InvoiceId);
        $item       = $collection->getFirstItem();
        $itemData   = $item->getData();

        if (empty($itemData['invoice_id'])) {
            $this->log->info("Order #$orderId ----- Get Payment Status - can not save transaction information into database due to pending payment or worng order id");
            return;
        }

        //save payment data
        $transaction = $data->focusTransaction;
        $this->isAddHistory = false;
        
        if (empty($itemData['payment_id']) || $itemData['payment_id'] != $transaction->PaymentId) {
            $item->setData('gateway_id', @$this->cards[$transaction->PaymentGateway]);

            $item->setData('gateway_transaction_id', $transaction->TransactionId);
            $item->setData('payment_id', $transaction->PaymentId);
            $item->setData('authorization_id', $transaction->AuthorizationId);
            $item->setData('reference_id', $transaction->ReferenceId);
            $item->setData('track_id', $transaction->TrackId);

            $item->setData('invoice_reference', $data->InvoiceReference);
            
            $this->isAddHistory = true;
        }


        $item->save();
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param \Magento\Sales\Model\Order $order
     * @param string $invoiceId
     * @param string $message
     */
    private function processPaidPayment($order, $invoiceId, $message) {
        if ($order->isCanceled()) {
            $this->unCancleorder($order, "MyFatoorah: remove the cancel status");
        }

        $orderStatus = $this->getGatewayConfig()->getMyFatoorahApprovedOrderStatus();
        if (!$this->isStatusExists($orderStatus)) {
            $orderStatus = $order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING);
        }
		
        //set order status
        $order->setState(Order::STATE_PENDING_PAYMENT)
                ->setStatus($orderStatus)
                ->addStatusHistoryComment($message)
                ->setIsCustomerNotified($this->getGatewayConfig()->isEmailCustomer());
        $order->save();

        //set payment
        $payment = $order->getPayment();
        $payment->setTransactionId($invoiceId);
        $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE, null, true);
        $order->save();

        if ($this->getGatewayConfig()->isAutomaticInvoice()) {
            $this->createMagentoInvoice($order, $invoiceId);
        }

        //send email
        $emailSender = $this->getObjectManager()->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
        $emailSender->send($order);
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param \Magento\Sales\Model\Order $order
     * @param string $message
     */
    private function processCancelPayment($order, $message) {
        if ($order->getState() === Order::STATE_PENDING_PAYMENT) {
            $order->registerCancellation($message)->save();
			
			$orderStatus = $this->getGatewayConfig()->getMyFatoorahApprovedOrderStatus();
			$order->setState(Order::STATE_PENDING_PAYMENT)
                ->setStatus($orderStatus)->save();
        } else if ($this->isAddHistory){
            $order->addStatusHistoryComment($message)->save();
        }
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param type $orderStatus
     * @return boolean
     */
    private function isStatusExists($orderStatus) {
        $statuses = $this->getObjectManager()
                ->get('Magento\Sales\Model\Order\Status')
                ->getResourceCollection()
                ->getData();

        foreach ($statuses as $status) {
            if ($orderStatus === $status['status']) {
                return true;
            }
        }

        return false;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param \Magento\Sales\Model\Order $order
     * @param string $invoiceId
     */
    private function createMagentoInvoice($order, $invoiceId) {
        $orderId = $order->getRealOrderId();

        $msgLog = "Order #$orderId ----- Get Payment Status";

        $this->log->info("$msgLog - In Create Invoice");
        if ($order->canInvoice()) {

            $invoice = $this->getObjectManager()->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order);
            if ($invoice->getTotalQty()) {
                $this->log->info("$msgLog - Can create an invoice.");
            } else {
                $this->log->info("$msgLog - Can't create an invoice without products.");
            }

            /*
             * Look Magento/Sales/Model/Order/Invoice.register() for CAPTURE_OFFLINE explanation.
             * Basically, if !config/can_capture and config/is_gateway and CAPTURE_OFFLINE and 
             * Payment.IsTransactionPending => pay (Invoice.STATE = STATE_PAID...)
             */
            $invoice->setTransactionId($invoiceId);
            $invoice->setRequestedCaptureCase(Order\Invoice::CAPTURE_OFFLINE);
            $invoice->register();

            $transaction = $this->getObjectManager()->create('Magento\Framework\DB\Transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
            $transaction->save();
        } else {
            $err = 'Can\'t create the invoice.';
            $order->addStatusHistoryComment("MyFatoorah: $err");
            $this->log->info("$msgLog - $err");
        }
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * https://magento.stackexchange.com/questions/297133/magento-2-2-10-change-cancelled-order-to-pending
     * 
     * @param \Magento\Sales\Model\Order $order
     * @param type $comment
     */
    public function unCancleorder($order, $comment) {

        $productStockQty = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $productStockQty[$item->getProductId()] = $item->getQtyCanceled();
            foreach ($item->getChildrenItems() as $child) {
                $productStockQty[$child->getProductId()] = $item->getQtyCanceled();
                $child->setQtyCanceled(0);
                $child->setTaxCanceled(0);
                $child->setDiscountTaxCompensationCanceled(0);
            }
            $item->setQtyCanceled(0);
            $item->setTaxCanceled(0);
            $item->setDiscountTaxCompensationCanceled(0);
        }

        $order->setSubtotalCanceled(0);
        $order->setBaseSubtotalCanceled(0);
        $order->setTaxCanceled(0);
        $order->setBaseTaxCanceled(0);
        $order->setShippingCanceled(0);
        $order->setBaseShippingCanceled(0);
        $order->setDiscountCanceled(0);
        $order->setBaseDiscountCanceled(0);
        $order->setTotalCanceled(0);
        $order->setBaseTotalCanceled(0);

        $order->setState(Order::STATE_PENDING_PAYMENT)->setStatus(Order::STATE_PENDING_PAYMENT);


        if (!empty($comment)) {
            $order->addStatusHistoryComment($comment);
        }

        /* Reverting inventory */
        $stockManagement = $this->getObjectManager()->create('\Magento\CatalogInventory\Api\StockManagementInterface');
        $itemsForReindex = $stockManagement->registerProductsSale(
                $productStockQty,
                $order->getStore()->getWebsiteId()
        );
        $productIds      = [];
        foreach ($itemsForReindex as $item) {
            $item->save();
            $productIds[] = $item->getProductId();
        }
        if (!empty($productIds)) {
            $stockIndexerProcessor = $this->getObjectManager()->create('Magento\CatalogInventory\Model\Indexer\Stock\Processor');
            $stockIndexerProcessor->reindexList($productIds);
        }
        $order->setInventoryProcessed(true);

        $order->save();
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
}
