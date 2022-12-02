<?php 

namespace Catchers\Custom\Observer;

class InvoiceEmailTemplateVars implements \Magento\Framework\Event\ObserverInterface
{
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$transportObject = $observer->getEvent()->getData('transportObject');
		$order = $transportObject->getData('order');
		$payment = $order->getPayment();
		$data = '';
		if ($payment->getData('po_number')) {
			$data = "customer order #" . $payment->getData('po_number');
		} 
		$transportObject->setData('po_number', $data);
	}
}