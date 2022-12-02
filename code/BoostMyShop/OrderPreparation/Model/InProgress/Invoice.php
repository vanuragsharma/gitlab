<?php

namespace BoostMyShop\OrderPreparation\Model\InProgress;


class Invoice
{
    protected $_invoiceService;
    protected $_invoiceSender;
    protected $_transaction;
    protected $_registry;
    protected $_orderFactory;
    protected $_orderItemFactory;

    public function __construct(
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \BoostMyShop\OrderPreparation\Helper\Transaction $transaction
    ) {
        $this->_invoiceSender = $invoiceSender;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_registry = $registry;
        $this->_orderFactory = $orderFactory;
        $this->_orderItemFactory = $orderItemFactory;
    }

    /**
     * @param $inProgress
     */
    public function createInvoice($inProgress, $invoiceItems = null)
    {
        //reload order to prevent erpcloud prefix removal
        $order = $inProgress->getOrder();
        $order = $this->_orderFactory->create()->load($order->getId());

        if ($invoiceItems == null)
            $invoiceItems = $this->prepareInvoiceItems($inProgress);

        $this->appendParents($inProgress, $invoiceItems);
        $this->addVirtualProducts($inProgress, $invoiceItems);

        $invoice = $this->_invoiceService->prepareInvoice($order, $invoiceItems);
        if ($invoice->canCapture())
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);

        $commentText = 'Packed by '.$inProgress->getOperatorName();
        $invoice->addComment(
            $commentText,
            false,
            false
        );
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);

        $this->_transaction->reset();
        $transactionSave = $this->_transaction->addObject($invoice)->addObject($invoice->getOrder());
        $transactionSave->save();

        $this->_invoiceSender->send($invoice);

        return $invoice;
    }

    /**
     *
     * @param $inProgress
     * @return array
     */
    protected function prepareInvoiceItems($inProgress)
    {
        $items = [];

        foreach($inProgress->getAllItems() as $item)
        {
            $items[$item->getitem_id()] = $item->getipi_qty();
        }

        return $items;
    }

    protected function appendParents($inProgress, &$invoiceItems)
    {
        foreach($inProgress->getAllItems() as $item)
        {
            if (isset($invoiceItems[$item->getitem_id()]) && ($invoiceItems[$item->getitem_id()] > 0))
            {
                if ($item->shipWithParent() && (!isset($invoiceItems[$item->getOrderItem()->getParentItemId()])))
                {
                    $parentItem = $this->_orderItemFactory->create()->load($item->getOrderItem()->getParentItemId());
                    $qty = min($item->getipi_qty(), $parentItem->getqty_ordered());
                    $invoiceItems[$item->getOrderItem()->getParentItemId()] = $qty;
                }

            }
        }
    }

    protected function addVirtualProducts($inProgress, &$invoiceItems)
    {
        foreach($inProgress->getOrder()->getAllItems() as $orderItem)
        {
            if ($orderItem->getproduct_type() == 'virtual' && $orderItem->getQtyToInvoice() > 0)
                $invoiceItems[$orderItem->getId()] = $orderItem->getQtyToInvoice();
        }
    }

}