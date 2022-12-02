<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Invoice;

class CreateInvoiceFromOrder extends \BoostMyShop\Supplier\Controller\Adminhtml\Invoice
{

    public function execute()
    {
        $poId = (int)$this->getRequest()->getParam('po_id');
        $order = $this->_orderFactory->create()->load($poId);

        $invoice = $this->_invoiceFactory->create()->createFromOrder($order);

        $this->messageManager->addSuccess(__('Invoice successfully created, please complete invoice details below'));
        $this->_redirect('supplier/invoice/edit', ['bsi_id' => $invoice->getId()]);
    }

}